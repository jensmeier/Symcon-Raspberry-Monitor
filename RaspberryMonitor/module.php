<?php

declare(strict_types=1);

/**
 * Symcon Raspberry Monitor 0.5 – absichtlich ausschließlich lesend.
 * Keine automatischen Netzwerkscans, Updates, Neustarts oder SSH-Schlüsseländerungen.
 */
class RaspberryMonitor extends IPSModule
{
    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyString('Devices', json_encode([
            ['name' => 'IP-Symcon', 'ip' => '192.168.178.41', 'mode' => 'local', 'user' => '', 'key' => ''],
            ['name' => 'Zigbee-Raspberry', 'ip' => '192.168.178.90', 'mode' => 'ssh', 'user' => 'pi', 'key' => '/root/.ssh/symcon_zigbee']
        ]));
        $this->RegisterPropertyInteger('Interval', 5);
        $this->RegisterPropertyInteger('ScanStart', 1);
        $this->RegisterTimer('RefreshTimer', 0, 'SRM_Refresh($_IPS[\'TARGET\']);');
        $this->RegisterVariableString('Overview', 'Raspberry Übersicht', '~HTMLBox');
        $this->RegisterVariableString('Details', 'Systemdiagnose');
        $this->RegisterVariableString('StatusJSON', 'Status (JSON)');
        $this->RegisterVariableString('NewIP', 'Neue Raspberry-IP', '~TextBox', 20);
        $this->RegisterVariableBoolean('AddIP', 'Gerät hinzufügen', '~Switch', 21);
        $this->RegisterVariableBoolean('RefreshNow', 'Jetzt aktualisieren', '~Switch', 22);
        $this->RegisterVariableBoolean('ScanNow', 'Netzwerk suchen (32 Adressen)', '~Switch', 23);
        $this->RegisterVariableString('Feedback', 'Bedienhinweis', '', 24);
        $this->RegisterVariableString('Found', 'Gefundene SSH-Geräte', '', 25);
        foreach (['NewIP', 'AddIP', 'RefreshNow', 'ScanNow'] as $ident) {
            $this->EnableAction($ident);
        }
        IPS_SetHidden($this->GetIDForIdent('StatusJSON'), true);
        IPS_SetHidden($this->GetIDForIdent('Details'), true);
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
        // Symcon 9: HTML-Inhalt statt Link/Webseite darstellen.
        IPS_SetVariableCustomPresentation($this->GetIDForIdent('Overview'), [
            'PRESENTATION' => VARIABLE_PRESENTATION_WEB_CONTENT,
            'HTML_TYPE' => 0
        ]);
        $this->SetTimerInterval('RefreshTimer', max(1, $this->ReadPropertyInteger('Interval')) * 60000);
        $this->Refresh();
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'NewIP':
                $this->SetValue('NewIP', trim((string)$Value));
                return;
            case 'RefreshNow':
                $this->Refresh();
                $this->SetValue('RefreshNow', false);
                $this->SetValue('Feedback', 'Status aktualisiert: '.date('d.m.Y H:i:s'));
                return;
            case 'AddIP':
                try {
                    $ip = trim($this->GetValue('NewIP'));
                    $this->AddDevice($ip);
                } catch (Throwable $e) {
                    $this->SetValue('Feedback', 'Hinzufügen: '.$e->getMessage());
                } finally {
                    $this->SetValue('AddIP', false);
                }
                return;
            case 'ScanNow':
                try {
                    $this->ScanNetwork();
                } catch (Throwable $e) {
                    $this->SetValue('Feedback', 'Suche: '.$e->getMessage());
                } finally {
                    $this->SetValue('ScanNow', false);
                }
                return;
            default:
                throw new Exception('Unbekannte Aktion');
        }
    }

    /** Fügt nur einen Konfigurationseintrag hinzu; richtet keine SSH-Anmeldung ein. */
    public function AddDevice(string $ip): void
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new Exception('Bitte eine gültige IPv4-Adresse eingeben.');
        }
        if (!$this->privateLAN($ip)) {
            throw new Exception('Nur private LAN-Adressen erlaubt.');
        }
        $devices = json_decode($this->ReadPropertyString('Devices'), true);
        if (!is_array($devices)) $devices = [];
        foreach ($devices as $d) {
            if (($d['ip'] ?? '') === $ip) {
                $this->SetValue('Feedback', 'IP '.$ip.' ist bereits eingetragen.');
                return;
            }
        }
        if (count($devices) >= 25) throw new Exception('Maximal 25 Geräte.');
        $devices[] = [
            'name' => 'Raspberry '.$ip,
            'ip' => $ip,
            'mode' => 'ssh',
            'user' => 'pi',
            'key' => '/root/.ssh/symcon_zigbee'
        ];
        IPS_SetProperty($this->InstanceID, 'Devices', json_encode($devices, JSON_UNESCAPED_UNICODE));
        IPS_ApplyChanges($this->InstanceID);
        $this->SetValue('Feedback', 'Gerät '.$ip.' gespeichert. SSH-Berechtigung in der Instanzkonfiguration prüfen.');
    }

    private function privateLAN(string $ip): bool
    {
        $n = ip2long($ip);
        if ($n === false) return false;
        $n = (int) sprintf('%u', $n);
        return ($n >= ip2long('10.0.0.0') && $n <= ip2long('10.255.255.255'))
            || ($n >= ip2long('172.16.0.0') && $n <= ip2long('172.31.255.255'))
            || ($n >= ip2long('192.168.0.0') && $n <= ip2long('192.168.255.255'));
    }

    /** Limitierter, manuell ausgelöster Suchlauf über 32 Adressen, keine automatische Aufnahme. */
    public function ScanNetwork(): void
    {
        $devices = json_decode($this->ReadPropertyString('Devices'), true);
        if (!is_array($devices)) $devices = [];
        $base = '192.168.178.';
        foreach ($devices as $d) {
            if (($d['mode'] ?? '') === 'local' && filter_var(($d['ip'] ?? ''), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $parts = explode('.', $d['ip']);
                $base = $parts[0].'.'.$parts[1].'.'.$parts[2].'.';
                break;
            }
        }
        $start = (int) $this->ReadPropertyInteger('ScanStart');
        $start = max(1, min(223, $start));
        $found = [];
        for ($i=$start; $i<$start+32; $i++) {
            $ip = $base.$i;
            $socket = @fsockopen($ip, 22, $errno, $errstr, 0.13);
            if ($socket !== false) {
                fclose($socket);
                $found[] = $ip;
            }
        }
        $this->SetValue('Found', $found ? implode(', ', $found) : 'Keine offenen SSH-Ports im Suchbereich.');
        $this->SetValue('Feedback', 'SSH-Port-Suche '.$base.$start.' bis '.$base.($start+31).' abgeschlossen. Ein gefundener Port beweist keinen Raspberry.');
    }

    public function Refresh(): void
    {
        $items = json_decode($this->ReadPropertyString('Devices'), true);
        if (!is_array($items)) {
            $items = [];
        }
        $states = [];
        foreach ($items as $item) {
            $states[] = $this->readDevice(is_array($item) ? $item : []);
        }
        $this->SetValue('StatusJSON', json_encode(['at' => date(DATE_ATOM), 'devices' => $states], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $lines = ['Raspberry Monitor 0.5 – '.date('d.m.Y H:i:s')];
        foreach ($states as $s) {
            $lines[] = sprintf('%s (%s): %s | OS: %s | Kernel: %s | CPU: %s | RAM: %s | HDD: %s | Updates: %s | Dienst: %s',
                $s['name'], $s['ip'], $s['online'] ? 'ONLINE' : 'OFFLINE', $s['os'], $s['kernel'], $s['temp'], $s['ram'], $s['disk'], $s['updates'], $s['service']);
            if ($s['error'] !== '') $lines[] = '  Hinweis: '.$s['error'];
        }
        $this->SetValue('Details', implode("\n", $lines));
        $this->SetValue('Overview', $this->render($states));
    }

    /** Prüft nur TCP-Port 22. Keine Aussage über gültige SSH-Zugangsdaten. */
    public function ProbeIP(string $ip): string
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return 'Ungültige IPv4-Adresse';
        }
        $socket = @fsockopen($ip, 22, $errno, $errstr, 1);
        if ($socket === false) return 'SSH-Port 22 nicht erreichbar: '.$ip;
        fclose($socket);
        return 'SSH-Port 22 erreichbar: '.$ip.' (Anmeldung noch nicht geprüft)';
    }

    private function run(string $cmd, bool $remote, string $ip, string $user, string $key): array
    {
        if ($remote) {
            $ssh = '/usr/bin/ssh -n -T -o BatchMode=yes -o StrictHostKeyChecking=yes -o ConnectTimeout=3 -o ConnectionAttempts=1 -o ServerAliveInterval=3 -o ServerAliveCountMax=1';
            $cmd = $ssh.' -i '.escapeshellarg($key).' '.escapeshellarg($user.'@'.$ip).' '.escapeshellarg($cmd);
        }
        $output = []; $code = 1;
        exec($cmd.' 2>&1', $output, $code);
        return [$code === 0, trim(implode("\n", $output))];
    }

    private function readDevice(array $device): array
    {
        $name = substr((string)($device['name'] ?? 'Unbenannt'), 0, 100);
        $ip = (string)($device['ip'] ?? '');
        $remote = ($device['mode'] ?? 'ssh') !== 'local';
        $user = (string)($device['user'] ?? 'pi');
        $key = (string)($device['key'] ?? '');
        $state = ['name' => $name, 'ip' => $ip, 'online' => false, 'os' => '–', 'kernel' => '–', 'temp' => '–', 'ram' => '–', 'disk' => '–', 'updates' => '–', 'service' => '–', 'error' => ''];
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) { $state['error'] = 'Ungültige IPv4-Adresse'; return $state; }
        if ($remote && (!preg_match('/^[a-z_][a-z0-9_-]{0,31}$/', $user) || !preg_match('#^/[a-zA-Z0-9/_\.\-]+$#', $key))) {
            $state['error'] = 'SSH-Nutzer oder Schlüsselpfad ungültig'; return $state;
        }
        // Genau ein kurzer SSH-Aufruf pro Raspberry; Ausgabe auf feste Zeilenzahl beschränkt.
        $read = <<<'SH'
LC_ALL=C; echo "OS=$(sed -n 's/^PRETTY_NAME=//p' /etc/os-release | head -n1 | tr -d '"')"; echo "KERNEL=$(uname -r)"; echo "TEMP=$(cat /sys/class/thermal/thermal_zone0/temp 2>/dev/null || echo NA)"; echo "RAM=$(awk '/MemTotal:/ {t=$2} /MemAvailable:/ {a=$2} END {if(t>0) printf "%.0f%%",(t-a)*100/t; else print "NA"}' /proc/meminfo)"; echo "DISK=$(df -P / | awk 'NR==2 {print $5}')"; echo "UPDATES=$(LC_ALL=C apt-get -s upgrade 2>/dev/null | awk '/^Inst / {n++} END {print n+0}')"; echo "SERVICE=$(systemctl is-active zigbee2mqtt 2>/dev/null || true)"
SH;
        [$ok, $output] = $this->run($read, $remote, $ip, $user, $key);
        if (!$ok) { $state['error'] = substr($output, 0, 220); return $state; }
        $state['online'] = true;
        foreach (explode("\n", $output) as $line) {
            if (!str_contains($line, '=')) continue;
            [$k, $v] = explode('=', $line, 2);
            $v = substr(trim($v), 0, 120);
            if ($k === 'OS') $state['os'] = $v;
            if ($k === 'KERNEL') $state['kernel'] = $v;
            if ($k === 'RAM') $state['ram'] = $v;
            if ($k === 'DISK') $state['disk'] = $v;
            if ($k === 'UPDATES') $state['updates'] = $v.' (APT-Listen)';
            if ($k === 'SERVICE') $state['service'] = $v;
            if ($k === 'TEMP' && is_numeric($v)) $state['temp'] = number_format(((float)$v) / 1000, 1, ',', '.').' °C';
        }
        return $state;
    }

    private function render(array $states): string
    {
        $html = '<div style="font-family:Arial,sans-serif;background:#f4f7fb;padding:12px;color:#203047"><h2 style="margin:0 0 12px">Raspberry Überwachung <small style="font-size:12px;color:#64748b">0.5 · '.date('d.m.Y H:i').'</small></h2><div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,320px),1fr));gap:14px">';
        foreach ($states as $s) {
            $e = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $badge = $s['online'] ? '<span style="color:#127444">● ONLINE</span>' : '<span style="color:#b91c1c">● OFFLINE</span>';
            $html .= '<section style="background:#fff;border:1px solid #dbe3ed;border-radius:12px;padding:16px;min-width:0"><h3 style="margin:0">'.$e($s['name']).'</h3><small>'.$e($s['ip']).'</small> · '.$badge.'<hr>';
            foreach (['os'=>'System','kernel'=>'Kernel','temp'=>'CPU','ram'=>'RAM','disk'=>'Speicher','updates'=>'Updates','service'=>'Zigbee-Dienst'] as $key=>$label) {
                $html .= '<div style="display:flex;justify-content:space-between;gap:14px;border-bottom:1px solid #eef2f5;padding:7px 0"><span>'.$label.'</span><strong style="text-align:right;overflow-wrap:anywhere">'.$e($s[$key]).'</strong></div>';
            }
            if ($s['error'] !== '') $html .= '<p style="color:#b91c1c">'.$e($s['error']).'</p>';
            $html .= '</section>';
        }
        return $html.'</div><p style="font-size:12px;color:#64748b">Nur lesend · keine Neustarts · keine Updates installiert. Gerät hinzufügen: IP in die Variable Neue Raspberry-IP eingeben und Gerät hinzufügen schalten. Schalter und Übersicht in dieselbe Visualisierungsseite aufnehmen.</p></div>';
    }
}
