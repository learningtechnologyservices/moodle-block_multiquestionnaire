moodle-block_questionnaire_manager
==================================
Questionnaire Manager block

License: GPL v3
Author: Learning Technology Services, www.lts.ie
Lead Developer: Bas Brands, www.basbrands.nl

Moodle version: 2.4

INTRO

This block has been created to enable quick copying / hiding / showing of a parent questionnaire in all course sections.


USAGE

Install the block in the /blocks/ folder
Unzip the plugin into folder "questionnaire_manager"
Navigate to Site Administration -> Notifications to start installation

Create a new course and add your Questionnaire

Turn editing on and add the Questionnaire Manager block
Click on the "gear" icon to configure the Questionnaire Manager block
Select your Questionnaire

Upload your CSV file and save

Make sure the CSV uses this sample format: (no comma's or quotes allowed)

shortname
HOME
TC
CC
M
QTI
QTE
test01
test02

The CSV file should have just one of these fields:
courseid, fullname, shortname


BLOCK FUNCTIONS (only for moodle managers / admins)

- Duplicate questionnaire.

Duplicate the master questionnaire into all courses in the CSV file, all child questionnaires are added into secion 0 of the child courses in a hidden state


- Show / Hide questionnaires. 

Show or hide all child questionnaires liked to the master questionnaire


- Copy this block

Copy this block into all child courses. This way the block becomes available to teachers in all courses. Teacher will only have the Show Responses option.


 -Show responses (only for teachers)

Shows a report with anonymous results from the current course. This option is not available to students and will not work in the master course.
