moodle-block_multiquestionnaire
==================================
Multiquestionnaire Manager block

License: GPL v3
Author: Learning Technology Services, www.lts.ie
Lead Developer: Bas Brands

New Comment

Moodle version: 2.4

Development sponsored by DCU, http://www.dcu.ie [^]

Prerequisite

In order for Multiquestionnaire Manager Block to work, the Questionnaire mod plugin needs to be already installed from: 
https://moodle.org/plugins/pluginversions.php?plugin=mod_questionnaire

INTRO

This block has been created to enable quick copying / hiding / showing of a parent questionnaire in all course sections.
This can be used to set up a master questionnaire on a separate master course and then clone it to any other courses as needed.


USAGE

Install the block in the /blocks/ folder
Unzip the plugin into folder "multiquestionnaire"
Navigate to Site Administration -> Notifications to start installation

Create a new course and add your Questionnaire

Turn editing on and add the Multiquestionnaire Manager block
Click on the "gear" icon to configure the Multiquestionnaire Manager block
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
