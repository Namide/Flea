# Flea

Version: 2.0

__Lightweight PHP framework__
- Quick to install
- Lightweight (fast processes)
- Cache Management
- GZip (CSS, JS, HTML)
- Simple and permissive urls
- Simple templating and content (migration easy)
- Support multi language
- Use CSV to manage pages meta datas (not content) - _New!_
- ~~Minify (CSS, JS, HTML)~~


__Requirements__
- PHP version 7.0 or greater  
- PDO (SqLite by default)


__Links__
- [Sources](https://github.com/namide/flea)
- [Documentation](http://flea.namide.com/doc/)

## Roadmap

### Todo

 - Active apache `mod_expires` in `.htaccess`
 - Clean redirect 301 from CSV and `content/initDB.php`


## Get started

### Initialize the languages

#### list of languages
`content/initLang.php`

```php
$lang->addDefault('en');
$lang->add('fr');
```


### Initialize the page

#### initialization
`pagelist.csv`

```csv

path
// exemple: pages/about

lang
// exemple: en

url
// exemple: en/about

template
// Name of the template	
// exemple: default

date
// Date of the creation of the page (for sorting)
// exemple: 2016-05-29

tags
// Add tags to the page
// exemple: page ; about ; skills

meta:{custom}
// Additional content, replace {custom} by the label of your content
// exemple: meta:title -> About me
// exemple: meta:description -> I'm a creative developper

metas
// like meta:{custom} but in one cell
// exemple: title : About me ; description : I'm a creative developper

visible
// Is the page visible ? (in the sitemap...)
// exemple: 1

cachable
// Is the page cachable ?
// exemple: 1

type
// Type of the page: 
// exemple: 
//			-> Nothing if the page is basic
// exemple: default
//			-> if it's the default page
// exemple: error404
//			-> if it's the error 404 page

get.enable
// Active the GET handler for this page
// exemple: 1

get.explicit
// If the GET is explicit the URL contains the labels of values.
// URL: www.flea.namide.com/games
// ( get.explicit == 1) -> www.flea.namide.com/games/page/2/tag/RTS
//						   array( 'page'=>2, 'tag'=>'RTS' );
//
// ( get.explicit == 0) -> www.flea.namide.com/games/2/RTS
//						   array( 2, 'RTS' );
// exemple: 1

301
// List of additional URL for 301 redirection
// exemple: about ; en/me

header
// Arguments to the php function header()
// of the page (for other type than HTML, like XML)
// example: Content-Type: application/xml; charset=utf-8'
```

### Content of the page

#### content
`pages/about/{language}.php`

```html
<article>
	<h1>Welcome on {{meta:title}}</h1>
	<p>{{meta:description}}</p>
	<img width="" height="" src="{{pageContentPath}}img/example.png" alt="image example">
</article>
```


### Flea variables

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

{{date}}
// Date of the current page

{{meta:title}}
// Title of the current page

{{body}}
// HTML body of the current page

{{meta:additionnal-custom-label-content}}
// $currentPage->getMetas()->getValue('additionnal-custom-label-content');

{{pageNameToAbsUrl:page-name}}
// $buildUtil->getAbsUrlByIdLang( ‘page-name', $currentLanguage );

{{urlPageToAbsoluteUrl:page-url}}
// $buildUtil->getAbsUrlByPageUrl( ‘page-url' );
```
