# Installation #

After downloading the plugin to the folder `moodle/question/type/` you need to rename the plugin folder itself to `moopt`.
Example: If you used `git clone` to clone the repository you probably have the following hierarchy: `moodle/question/type/moodle-qtype_moopt/`. Rename this to `moodle/question/type/moopt/`.

# Programmieraufgabe #


The attempt overview for the teacher is partly wrong for programming tasks, because mod_quiz isn't intended to be used with asynchronously graded questions. This results in a mismatch between the actual question state and the question state that is display to the user. The main problem is that in moodle there is no such state as "the student has submitted the answer, the question is therefore answered and finished but not yet graded". Some existing states do almost match on this but not entirely. This results in a display problem that is explained in the following:
* ![](doc/img/x.png) 
See question 5: The submission has been sent to the grader for grading but there is no result yet
* ![](doc/img/0x.png) 
See question 4: Either there was a submission and it has been graded with 0.0 or there wasn't a submission in the first place
* ![](doc/img/regrade.png)
Regrading has two problems: 
  1. The value in the column "Regrade" ("Done") is wrong. The submissions have been sent to the grader for regrading therefore the regrading process is finished from moodles point of view although in fact it is not.
  2. moodle will always display "[old value]/0.0" for each questions score. This could either mean that the grade process hasn't finished yet or that it did finish and the question has been graded with 0.0. Once the grade process finishes the score will be updated correctly. If, however, the graded score is indeed 0.0 the teacher will never know if the grade process has already finished or not (at least from the overview). 

## License ##

2019 ZLB-ELC Hochschule Hannover <elc@hs-hannover.de>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <http://www.gnu.org/licenses/>.
