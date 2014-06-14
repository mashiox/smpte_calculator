smpte_calculator
================

A calculator for finding dropframe timecodes

Division Algorithm (a proof for DF timecodes)
a = dq + r
q = a div d = floor(a/d)
r = a mod d
The dropcode alorithm drops the first (zeroth) and second (first) frame from the time sequence.
We must manipulate the division algorithm to `redefine` the quotient of some frame number when divided by 17982 (significance of 17982 unknown)
We do this by keeping the remainder of a mod d the same and forcing the quotient to be zero when i should be 0 or 1, and the dividend to be by the remainder.
In this way we preserve the correctness of the division algorithm, while achieving the desired result: dropped frames.

http://andrewduncan.net/timecodes/ 
http://forum.doom9.org/archive/index.php/t-132432.html

Input Domain: (You must validate)
hour member of Z+
0 <= minute <= 59 (Subset of Z)
0 <= second <= 59 (Subset of Z)
0 <= frame <= 29  (Subset of Z)

This documentation used mathematical notation for sets.
Z+ : Positive Integers {1, 2, 3, ...}
Z : Integers { ..., -3, -2, -1, 0, 1, 2, 3, ... }
floor(a/b) = a div b

