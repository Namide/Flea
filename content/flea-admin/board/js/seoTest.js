function SeoTest(pList)
{
	this.id = 0;
	this.list = pList;
	this.xmlhttp;
}
SeoTest.prototype =
		{
			start: function (elementId)
			{
				document.getElementById(elementId).innerHTML = 'started';
				seoTest.testPage();
			},
			testPage: function ()
			{
				document.getElementById(seoTest.list[seoTest.id]["id"]).innerHTML = "pending";//xmlhttp.responseText;

				seoTest.xmlhttp = new XMLHttpRequest();
				seoTest.xmlhttp.open("GET", seoTest.list[seoTest.id]["url"], true);
				seoTest.xmlhttp.onreadystatechange = function ()
				{
					if (seoTest.xmlhttp.readyState == 4 && (seoTest.xmlhttp.status == 200 || seoTest.xmlhttp.status == 404))
					{
						var pageContent = seoTest.xmlhttp.responseText;
						if (pageContent == undefined)
							pageContent = string(seoTest.xmlhttp.responseXML);
						document.getElementById(seoTest.list[seoTest.id]["id"]).innerHTML = seoTest.analysePage(pageContent);
						seoTest.id++;
						if (seoTest.id < seoTest.list.length)
						{
							seoTest.testPage();
						}
					}
				}
				seoTest.xmlhttp.send();
			},
			analysePage: function (page)
			{
				var headTitle = seoTest.getTagContent(page, "<title>", "</title>");
				var contentBrut = seoTest.getBrutContent(page);
				var metaDescription = seoTest.getMetaDescription(page);
				var h123456 = seoTest.getTagsContent(page, "<h1", "</h1>", false);
				var a = seoTest.getTagsContent(page, "<a", "</a>", false);
				var p = seoTest.getTagsContent(page, "<p", "</p>", false);
				var alt = seoTest.getTagsContent(page, "alt=\"", "\"", true);

				var numErrors;
				var valid = (headTitle.length > 0 && headTitle.length < 65);
				var resume = seoTest.getSpanText("title: " + headTitle.length + " chars", valid, " ( < 65 )");

				valid = (contentBrut.split(" ").length > 300 && contentBrut.split(" ").length < 500);
				resume += seoTest.getSpanText("content: " + contentBrut.split(" ").length + " words", valid, " ( 300 < words < 500 )");

				valid = (metaDescription.length > 0 && metaDescription.length < 150);
				resume += seoTest.getSpanText("meta-description: " + metaDescription.length + " chars", valid, " ( chars < 150 )");


				valid = 0;
				for (var i = 0; i < h123456.length; i++)
				{
					h123456[i] = seoTest.getBrutContent(h123456[i]);
					if (h123456[i].length > 55) {
						valid++;
					}
				}
				resume += seoTest.getSpanText((valid) + "/" + h123456.length + " titles (h1, h2...) too longs", (valid < 1), " ( chars < 55 )");

				valid = (a.length < 100);
				resume += seoTest.getSpanText("num links: " + a.length, valid, " ( links < 100 per page )");


				valid = 0;
				for (var i = 0; i < p.length; i++)
				{
					p[i] = seoTest.getBrutContent(p[i]);
					if (p[i].split(" ").length > 85) {
						valid++;
					}
				}
				resume += seoTest.getSpanText((valid) + "/" + p.length + " paragraph (p) too longs", (valid < 1), " ( words < 85 )");



				numErrors = 0;
				for (var i = 0; i < alt.length; i++)
				{
					if (alt[i].length > 60) {
						numErrors++;
					}
				}
				resume += seoTest.getSpanTextInline((numErrors) + "/" + alt.length + " alt too longs", (numErrors < 1), " ( chars < 60 )");
				numErrors = 0;
				for (var i = 0; i < alt.length; i++)
				{
					if (alt[i].length < 1) {
						numErrors++;
					}
				}
				resume += seoTest.getSpanText((numErrors) + "/" + alt.length + " empty", (numErrors < 1), " ( chars > 0 )");



				return resume;
			},
			getMetaDescription: function (text)
			{
				var a = text.split("<meta");
				for (var i = 0; i < a.length; i++)
				{
					var b = a[i].split("name=\"description")
					if (b.length > 1)
					{
						var c = a[i].split("content=\"");
						if (c.length > 1)
						{
							return c[1].split("\"")[0];
						}
					}
				}
				return "";
			},
			getSpanText: function (text, valid, error)
			{
				return '<span style="color:' + ((valid) ? "green" : "red") + '">' + text + ((valid) ? "" : error) + '<br>';
			},
			getSpanTextInline: function (text, valid, error)
			{
				return '<span style="color:' + ((valid) ? "green" : "red") + '">' + text + ((valid) ? "" : error) + " ";
			},
			getTagContent: function (text, tag1, tag2)
			{
				var a = text.split(tag1);
				if (a.length > 1)
				{
					a = a[1].split(tag2);
					if (a.length > 1)
					{
						return a[0];
					}
				}
				return "";
			},
			getTagsContent: function (text, tag1, tag2, closed)
			{
				var a = text.split(tag1);
				var output = [];
				while (a.length > 1)
				{
					b = a[1].split(tag2);
					if (b.length > 1)
					{
						if (closed)
						{
							b = b[0];
						} else
						{
							b = b[0].split(">");
							b.shift();
							b = b.join(">")
						}
						output.push(b);
					}
					a.shift();
				}
				return output;
			},
			getBrutContent: function (text)
			{
				var t = text;
				var a = text.split("<body>");
				if (a.length > 1)
				{
					a.shift();
					t = a[0];
				}

				a = t.split("<");
				for (var i = 0; i < a.length; i++)
				{
					var b = a[i].split(">");
					if (b.length == 2 && b[1] != "")
						a[i] = b[1];
					else
						a[i] = "";
				}
				t = a.join(" ");

				t = t.split("\t").join(" ");
				t = t.split("\n").join(" ");
				while (t.charAt(0) == " ") {
					t = t.substring(1);
				}
				while (t.charAt(t.length - 1) == " ") {
					t = t.substring(0, t.length - 1);
				}
				while (t.split("  ").length > 1)
				{
					t = t.split("  ").join(" ");
				}

				return String(t);
			}

		};