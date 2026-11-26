#!/usr/bin/env python3
"""Independent cooling policy. Hardware deliberately not enabled by default."""
import time
import math

CPU_CURVE = [(45,30),(50,50),(55,70),(60,85),(65,100)]
SSD_CURVE = [(35,30),(40,50),(45,70),(50,85),(55,100)]

def demand(value, curve):
    return max((power for threshold,power in curve if value >= threshold), default=0)

def valid(value):
    return isinstance(value,(int,float)) and not isinstance(value,bool) and math.isfinite(value) and -20 <= value <= 125

class Cooling:
    def __init__(self):
        self.tests = {}
        self.manual = {}
        self.previous = [100,100]
        self.changed = [0,0]

    def test(self, fan, power, seconds, now=None):
        if fan not in (1,2) or power not in (25,50,75,100) or seconds not in (10,30,60,120):
            raise ValueError("Invalid test")
        self.tests[fan] = (power,(time.monotonic() if now is None else now)+seconds)

    def stop_test(self, fan):
        self.tests.pop(fan,None)

    def calculate(self, cpu, ssd, require_ssd=True, now=None):
        now = time.monotonic() if now is None else now
        emergency = not valid(cpu) or cpu >= 75 or (require_ssd and not valid(ssd)) or (valid(ssd) and ssd >= 65)
        auto = 100 if emergency else max(demand(cpu,CPU_CURVE), demand(ssd,SSD_CURVE) if valid(ssd) else 0)
        output = []
        for fan in (1,2):
            test = self.tests.get(fan)
            if test and now >= test[1]:
                self.tests.pop(fan,None)
                test = None
            target = 100 if emergency else test[0] if test else self.manual.get(fan,auto)
            # Automatic downshifts only after a minimum run time; emergency always wins.
            i=fan-1
            if not emergency and not test and fan not in self.manual and target < self.previous[i]:
                if now-self.changed[i]<30:
                    target=self.previous[i]
                elif max(demand(cpu+2,CPU_CURVE),demand(ssd+2,SSD_CURVE) if valid(ssd) else 0)>=self.previous[i]:
                    target=self.previous[i]
            if target != self.previous[i]:
                self.changed[i]=now
            self.previous[i]=target
            output.append(target)
        return {"emergency":emergency,"target":output}
