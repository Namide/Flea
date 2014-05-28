Flea
====

Lightweight PHP framework
- Quick to install
- No database
- Cache Management
- Simple and permissive urls
- Lightweight


Initialize the languages
------------------------

##### list of languages
`content/initLang.php`

```php
$lang->addDefault('en');
$lang->add('fr');
```


Initialize the page
------------------------

##### initialization
`content/home/{language}-init.php`

```php
// URL
$url = 'en/home';
// Additional URL
$addUrl = 'en/homepage';
// List of additional URL
//$addUrls = array('en/homepage/1', 'en/homepage/2'); 
// Name of the template	
$template = 'default';

// Is the page visible ? (in the sitemap...)
$visible = true;
// Is the page cachable ?
$cachable = true;

// Active the GET handler for this page
$getEnabled = false;
// If the GET is explicit the URL contains the labels of values.
// URL: www.flea.namide.com/games
// GET: array( 'page'=>2, 'tag'=>'RTS' );
// ( explicit ) www.flea.namide.com/games/page/2/tag/RTS
// ( !explicit ) www.flea.namide.com/games/2/RTS
$getExplicit = true;
// Date of the creation of the page (for sorting)
$date = '2014-05-01';

$htmlBody = '<h1>Page d\'accueil</h1>';
// description of the page
$htmlDescription = 'FWK is a really fun framework!';
// Additional tags in the head (like CSS, JS, meta...)
$htmlHeader = '<meta name="robots" content="all" />';
// title of the page
$htmlTitle = 'accueil';

// Type of the page: 
// - ''
// - 'default' if it's the default page
// - 'error404' if it's the error 404 page
$type = 'default';
// Arguments to the php function header()
// of the page (for other type than HTML, like XML)
//$phpHeader = 'Content-Type: application/xml; charset=utf-8';	

// Add tags to the page
$tags = array('importantPage', 'mainlyPage');
// Add 1 tag to the page		
$tag = 'konamiCodeEnabled';
// Add additionals contents accessible
// (from other pages or with the template)
$contents = array( 'resume'=>'Home page', 'important'=>3 );
```

Content of the page
------------------------

##### content
`content/home/{language}-build.php`

```html
<article>
	<h1>Welcome on {{title}}</h1>
	<p>It's you home page.</p>
	<img width="" height="" src="{{pageContentPath}}img/example.png" alt="image example">
</article>
```

Flea variables
------------------------

Used in the build page `content/home/{language}-build.php`
of init page `content/home/{language}-init.php`
or template

```php
{{rootPath}}
// URL of the root

{{templatePath}}
// URL of the template directory

{{contentPath}}
// URL of the content directory

{{pageContentPath}}
// URL of the page in the content directory

{{lang}}
// Current language

{{title}}
// Title of the current page

{{header}}
// HTML header of the current page

{{body}}
// HTML body of the current page

{{description}}
// HTML description of the current page

{{content:additionnal-label-content}}
// $currentPage->getContent('additionnal-label-content');

{{pageNameToAbsUrl:page-name}}
// $buildUtil->getAbsUrlByIdLang( ‘page-name', $currentLanguage );
```
