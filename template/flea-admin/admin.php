<!DOCTYPE html>
<html lang="{{lang}}">

<head>

    <meta charset="utf-8">
	<title>{{title}} - admin</title>
    <meta name="description" content="{{description}}" />
	
	
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
    <meta name="viewport" content="width=device-width; height=device-height; maximum-scale=1.4; initial-scale=1.0; user-scalable=yes" />
	
	<meta name="author" content="Namide" />

	<link rel="stylesheet" type="text/css" href="{{templatePath}}flea-admin/css/admin.css" />
	
    <link rel="icon" type="image/png" href={{templatePath}}img/favicon.png" /> 
    
    {{header}}
    
</head>

<body>
    
    <header>
        <h1>Backoffice</h1>
        <nav>
			
			<ul>
				<?php
				
					$query = Flea\SqlQuery::getTemp();
					$query->setWhere(' _lang = \'en\' AND page_prop = \'_tags\' AND value = \'flea-admin\' ');
					foreach( Flea::getPageList()->getByList( $query ) as $pageTemp )
					{
						echo Flea::getTagUtil()->getLink($pageTemp->getName());
					}
				?>
			</ul>
				
        </nav>
    </header>
    
    <section>
    
        {{body}}
		
    </section>
    
    <footer>
        
    </footer>
	
</body>

</html>