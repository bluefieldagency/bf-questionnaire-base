# Possible options for each of the classes, used in the Questionnaire tool

### Answer

score | int, what score does this answer score you?

data_type | string, type of data this answer is, for example it can be yes, no, unknown, not_relevant

check_method | string, an answer can have a check method value of 'disable_rest', for a multi checkbox question with "other" as one of the options

skip_to | int, what question to skip to, when this answer is selected? For radio buttons and stars

min | int, the lowest possible value of a range slider

max | int, the highest possible value of a range slider

step | int, the steps a range slider iterates by

### Page

container_border | boolean, determines if the questions on this page should have a border at the bottom

pdf_include_date | boolean @ dpia-online, should this page contain the 'created at' date in the pdf?

pdf_pagebreak | boolean @ dpia-online, should this page be on a new page, or continue after the previous page in the pdf?

### Question

extra_info | string

extra_info_triggered | boolean, defines if the extra info should be hidden behind a (i) icon

allow_additional_uploads | boolean, can you upload extra information for this question, like a PDF?

additional_upload_max | int, how many additional upload fields should there be? For text (like) and textarea types

additional_upload_min | int, how many of those additional upload field should be required?

child_triggered | boolean, should the additional question be triggered by a specific answer, or always shown?

answer_trigger | string, if this question is a child of another question, this defines what answer data_type triggers this additional (child) question to be shown

score_max | int, how many points could you possibly score for this question? Used in multi checkbox questions

placeholder | string, what placeholder should this input show?

columns | int, the number of columns multiple checkboxes should be displayed in

hide_disclaimer | boolean, should the email disclaimer be outputted? That will show how BF will use their data

question_code | string @ kto, defines a question to be of type 'nps score'

### Questionnaire

requires_invite | boolean, if this questionnaire requires an invite hash URL to be let in or not

route_prefix | string, what prefix should be used in the naming of routes? Defaults to questionnaire

intermediate_store_allowed | boolean, should the page show the "save progress" link at the top?

contact_form_enabled | boolean, should the page show the "contact us" link at the top?

admin_mail_intro | string, an intro text for the admin notification email

show_fixed_data_types | boolean, should the fixed data types (found in the QuestionnaireEntry class) be put in the admin notification mail?

### QuestionnaireInvite

for_departments | comma separated values @ kto

page_ids | comma separated IDs of pages to show to the invitee

### QuestionType

placeholder | string, what placeholder should this input show? Gets overwritten by a question placeholder option

