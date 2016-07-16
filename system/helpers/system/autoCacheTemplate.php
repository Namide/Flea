<?php
/*
 * The MIT License
 *
 * Copyright 2015 damien.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

include_once( 'config.php' );
include_once _SYSTEM_DIRECTORY . 'init/checkDir.php';
include_once _SYSTEM_DIRECTORY . 'init/import.php';
include_once _SYSTEM_DIRECTORY . 'init/loadPages.php';

\Flea::getUrlUtil()->reset();
\Flea::getBuildUtil()->reset();
?>

<html>
	<head>
		<title>Load all pages to cache there</title>
		<meta charset="utf-8">
	</head>
	<body>

<?php
$request = \Flea\SqlQuery::getTemp(\Flea\SqlQuery::$TYPE_SELECT);
$request->setWhere('(_visible > -1 OR _visible < 0) AND _cachable = 1');
$pages = Flea\PageList::getInstance()->getAll($request);
?>

		<h1>Load all pages to cache there (<?= count($pages); ?> pages)</h1>
		<p>Wait until all pages are loaded before leaving this page</p>

<?php
foreach ($pages as $page) {
	$absUrl = \Flea\BuildUtil::getInstance()->getAbsUrlByPage($page);
	?>

			<div style="float:left; width:320px;">
				<div>
					<a href="<?= $absUrl ?>"><?= $page->getPageUrl() ?></a><br>
			<?= $page->getName() . ' (' . $page->getLang() . ')' ?>
				</div>
				<iframe src="<?= $absUrl ?>" width="320" height="420"></iframe>
			</div>

				<?php } ?>

	</body>
</html>

		<?php
		unlink('cache.php');
		