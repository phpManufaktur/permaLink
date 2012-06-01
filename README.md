### permaLink

A Admin-Tool for the Content Management Systems [WebsiteBaker] [1] and [LEPTON CMS] [2] with enables creating and managing permanent links.

You may use the backend tool to create permaLinks or you can use the permaLink interface within your own add-ons.

#### Requirements

* minimum PHP 5.2.x
* using [WebsiteBaker] [1] _or_ using [LEPTON CMS] [2]
* [dbConnect_LE] [4] must be installed 
* [kitTools] [5] must be installed

#### Installation

* download the actual [permaLink_x.xx.zip] [3] installation archive
* in CMS backend select the file from "Add-ons" -> "Modules" -> "Install module"

#### First Steps

In the CMS backend select "Admin-Tools" -> "permaLink", choos the tab "Edit" and insert the Redirect URL with all assigned parameters for which a permanent link should be created. You can create a permanentLink for each page directory and each page name you wish - there a no restrictions.

You can access *kitTools* too from your own addons:

    require_once WB_PATH.'/modules/perma_link/class.interface.php';
    
    $permaLink = new permaLink();
    
    $redirect_url = 'http://sample.de/pages/something/any_url.php?parameter_1=one&parameter_2=two';
    $perma_link = '/sample.php';
    
    if (!$permaLink->createPermaLink($redirect_url, $perma_link, 'Admin')) {
      // error while creating the permaLink, get extend informations
      if ($permaLink->isMessage()) {
        // minor error - only prompt a message
        echo $permaLink->getMessage();
      }
      else {
        // fatal error - prompt extended informations
        echo $permaLink->getError();
      }
    }

this is the shortest and easiest way to create a permaLink. The sample will create the physical page

    http://sample.de/pages/sample.php
    
and - if called - will execute the origin url:

    http://sample.de/pages/something/any_url.php?parameter_1=one&parameter_2=two

Please have look into the class permaLink to get informations about the supported functions and additional features of the library.  

[1]: http://websitebaker2.org "WebsiteBaker Content Management System"
[2]: http://lepton-cms.org "LEPTON CMS"
[3]: https://github.com/phpManufaktur/permaLink/downloads
[4]: https://github.com/phpManufaktur/dbConnect_LE/downloads
[5]: https://github.com/phpManufaktur/kitTools/downloads
