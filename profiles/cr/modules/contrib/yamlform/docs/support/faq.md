Frequently Asked Questions (FAQ)
--------------------------------

### Common Questions

**Who are you?**

Hi, I am Jacob Rockowitz (aka [jrockowitz](https://www.drupal.org/u/jrockowitz) on D.O)
and I have building custom enterprise CMS solutions for the past 15 years.
I have been active in Drupal community since 2009. Please visit 
[my website](http://thebigbluehouse.com)
to learn more about me.

**Why did you build this module?**

I was the lead developer responsible for migrating, architecting, and building 
Memorial Sloan Kettering's current Drupal 8 website.  Memorial Sloan Kettering
was one of the largest early adopters of Drupal 8, launching a 30,0000+ page
website using a Beta release of Drupal 8.

Learn more about [Memorial Sloan Kettering's early adoption of Drupal 8](https://events.drupal.org/losangeles2015/sessions/adventures-drupal-8-how-memorial-sloan-kettering-made-leap-enterprise-d8).

**Where did you get this idea?**

The Webform module was not available for Drupal 8, and MSKCC needed a form builder
and submission manager. We had to come up with a quick and simple D8 replacement
for the Webform module. 

**How did you build this module?**

The original concept for the YAML Form module was to provide the simplest UI that required the least amount of work for building forms. Building a user interface
is a lot of work, while editing YAML files required very little work to set up.
Serializing renders array into editable YAML with some Form API documentation 
allowed MSK's site builders to build and manage hundreds of forms.

For the past 6+ months, I have been building out and improving this 
module iteratively, trying to reach feature parity with the Webform module and other online 
form builders, such as Wufoo and Gravity Forms.

**What are you planning on doing with this module?**

For now my goal is simply to publish a release candidate. In the long term,
I would like the YAML Form module to continue to leverage improvements in Drupal
core and hopefully become an important must-have contrib module for Drupal 8. 

My dream is to convince the Drupal Association to stop using Survey Monkey.

Finally, I need the Drupal community's help in figuring out how to I can
continue to support this module while still paying my bills.

### Specific Questions

**Is the YAML Form module a replacement for the Drupal's 7 Webform module?**

Originally, the YAML Form module was meant to be a temporary form building 
solution while the Webform module was ported to Drupal 8. It now seems unlikely 
that the Webform module will be ported to Drupal 8. Over the past year, the 
YAML Form module has nearly reached feature parity with the Webform module. So 
the answer is **YES**, the YAML Form module can be considered a Drupal 8 
replacement for the Webform module.

Learn more about the effort to [port Webform to Drupal 8](https://www.drupal.org/node/2075941).

**What Webform & EntityForm features are currently missing from the YAML Form module?**

Form settings 

- Limit submission by IP and/or cookie
- Resetting sid per form

Results

- Analysis of submitted data
- Submission sorting

Download

- Tracking last downloaded sid

> Analytics and reporting will never be included in the main YAML Form module. 
>
> Analytics and reporting should be handled by a dedicated module, or by a third party service.

**What are the alternatives to form?**

In Drupal 7, and now in Drupal 8, there are two primary approaches to form builder 
modules: Webform and Entity Form. Webform (and now YAML Form) uses Drupal's 
Form API (FAPI) to build forms with an Entity–attribute–value (EAV) table to 
store submissions. Entity Forms (now EForm and Contact Storage in D8) uses 
Drupal's Field API to build forms that store submissions in field-specific 
tables. There are benefits and downsides to both approaches. Since 
FAPI and EAV are simpler than the Field API, the YAML Form module is able to 
provide a leaner and faster UI for building forms and managing submissions. 
Meanwhile, using Drupal's Field API allows submissions to be customized, 
formatted, and managed using multiple displays and form modes with full Views 
integration, with extra functionality provided by Field API related 
contrib modules.

If you need robust reporting and submission management, you should use an 
entity based form builder, such as Contact Storage. If you just want to build a 
form and collect data, the YAML Form module is going to be the simpler solution. 

Learn more about the [Contact Storage](https://www.drupal.org/project/contact_storage) module.


### Contributing Questions

**How can I help?**

I have a lot of experience building and maintaining full applications, so I 
am reasonably comfortable managing the gestalt of the YAML Form module. My 
biggest challenge and limitation is writing documentation. Simply put, I am 
just more productive at writing code.

I do need help with documentation, Views integration, templating, and the 
front-end user interface.

I also need site builders to test the module, and developers to review the APIs. 

Finally, I need your help to spread the word about how much you like the 
YAML Form module by tweeting and writing blog posts.

**Can I donate to your cause?**

Right now, I am okay and have steady work with MSKCC. In the long term, I am 
most likely going to have to ask for donations and sponsorship. My goal for now is to get a stable release out to the Drupal community.

Still, it would seem silly for me _not_ to provide a donate button. 

<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="hosted_button_id" value="QWY4GWMGNQ9RN" />
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate" />
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>


**Can I hire you to help my organization implement and/or migrate from D6/D7 to 
the YAML Form module?**

Absolutely, I look forward to helping anyone migrate and adopt the YAML Form 
module.

Please [contact me](https://www.drupal.org/user/371407/contact).


### Troubleshooting

**How to debug issues with form elements/elements**

- A form's element is just a [Form API(FAPI)](https://www.drupal.org/node/37775)
  [render array](https://www.drupal.org/developing/api/8/render/arrays). 

- Some issues can be fixed by reading the API documentation associated 
  with a given [form element](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21Element%21FormElement.php/class/FormElement/8).
  Links to a form element's API documentation are included in the Elements 
  overview. (/admin/structure/yamlform/settings/elements)

**How to get help fixing issues with the YAML Form module**

- Review the YAML Form module's [issue queue](https://www.drupal.org/project/issues/yamlform) 
  for similar issues.

- If you need to create a new issue, **please** create and export an example of 
  the broken form configuration.   
  _This will help guarantee that your issue is reproducible._  

- Please also read [How to create a good issue](https://www.drupal.org/issue-queue/how-to)
  and use the [Issue Summary Template](https://www.drupal.org/node/1155816)
  when creating new issues.
