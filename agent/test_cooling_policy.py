import unittest
from cooling_policy import Cooling
class Tests(unittest.TestCase):
    def test_emergency_overrides_test(self):
        c=Cooling(); c.test(1,25,60,0)
        self.assertEqual(c.calculate(80,30,now=1)["target"],[100,100])
    def test_missing_sensor(self):
        self.assertTrue(Cooling().calculate(50,None)["emergency"])
    def test_independent_and_expiry(self):
        c=Cooling(); c.test(1,25,10,0); c.test(2,75,30,0)
        self.assertEqual(c.calculate(50,40,now=1)["target"],[25,75])
        self.assertEqual(c.calculate(50,40,now=11)["target"][0],50)
        self.assertNotIn(1,c.tests)
    def test_invalid_test(self):
        with self.assertRaises(ValueError): Cooling().test(3,100,60)
if __name__=="__main__": unittest.main()
