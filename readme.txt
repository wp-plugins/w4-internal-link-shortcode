=== W4 Internal Link Shortcode ===
Plugin Name: W4 Internal Link Shortcode
Author: sajib1223, Shazzad Hossain Khan
Donate link: http://w4dev.com/
Tags: links, shortcode, post links, page links, category links, author links
Requires at least: 2.9
Tested up to: 3.0.3
Stable tag: 1.3

Wordpress plugin for putting internal links with shortcode..

== Description ==
Now you can easily embed your wordpress sites internal links (of post, page ,category, author) in post content, page content or category description area. Just write the appropriate shortcode, and you are done. No need to update these links whenever you make any changes to the real link location.

<a href="http://w4dev.com/w4-plugin/w4-internal-link-shortcode/">Let us know your thought</a>.

Add link from other blog of a network( V.1.3 ) by blog id. use <code>blog_id</code> attribute to do that. This feature only supports for post link with <code>id</code>, not <code>name</code> or <code>slug</code>. Example: <pre><code>[intlink type="post" id="2" blog_id="3"]</code></pre>
This will generate link for post id 2 from the blog having id 3.

= Upgrade Notice =
* Link to a post from another blog on a multisite network ( V.1.3).
* New attribute ("target") introduced in Version 1.2. "target" attribute is same as a HTML anchor link target.(Recommend by "Simon")
* New attribute ("before" and "after") introduced in Version 1.1. These are for putting texts on before or after the link.

= Example: =

The default category link of your site should look like something http://example.com/category/uncategorized/. A shortcode for displaying this link should be..
<pre>[intlink type="cat" slug="uncategorized"]</pre>

Now, if you change your category base to cats, like http://example.com/cats/uncategorized/ , you don't have to update the shortcode. It will take the latest format to show the link.

= Arguments: =

* blog_id = default 'null'. (Show link from another blog on same network)
* type = default 'post'. (Your link type:post/page/cat/author).
* id = default ''. (This is the post/page/author/category id based on the type you have chosen).
* text = default based on the type. Post/Page title for type 'post' or 'page', Category name for 'cat', author display name for 'author'. ( the text to show inside the link).
* name = default ''. (Name of the link item object. Ex: "Uncategorized" for category Uncategorized, "Hello world" for the post Hello world).
* slug = default ''. (Slug of the link item object. Ex: "hello-world" for the post Hello world).
* class = default 'w4_internal_link'. (A HTML class for the link element "a").
* before = default ''. (Put text or html element before the link).
* after = default ''. (Put text or html element after the link).


= Notes =

* Remember,you have to write the shortcode properly. Script has a priority for finding a link from your shortcode.
* Firstly, the script will try to find the link type. if you haven't entered any type in your shortcode, it will assume the link as post/page link.
* Secondly, it will find your given attribute(id,name,slug) for the link. By priority, it will look for the "id" first. if id not found,then it will look for the "name". And last it will look for the slug.Slug is pretty eiser than name,as it contains all small letter and no space. If the is a name "I love Wordpress",the slug should be for this "i-love-wordpress",there is some exception although.
* Lastly, it will fletch the link based on the matched attribute and its parameter.


= Similar attributes and parameters =
Attributes:
* "tr|Tr|tar|targat|_target|terget|openin|newpage" for target

Parameters:
* "p|P|post|posts|page|pages" for "post"
* "c|C|cat|cats|category" for "cat"
* "a|A|auth|aothor|author" for "author"
* "p|parent|own|this|self|no" for target parameter "_parent"
* "b|blank|new|another|out|yes" for target parameter "_blank"


-- We have added similar attributes and parameters to give you a result at least. Please try using appropriate attributes and parameters. --


If you are unable to use/manage this plugin or if you have some suggestions to improve this plugin <a href="http://w4dev.com/w4-plugin/w4-internal-link-shortcode/">Contact us</a>.


== Installation ==
1. Upload plugin zip file to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==
= Where i can use it =
You can use it in post, page and in category description field.


== Screenshots ==

== Changelog ==
= 1.1 =
New attribute ("before" and "after") introduced for putting texts on before or after the link.


== Upgrade Notice ==
= 1.1 =
New attribute ("before" and "after") introduced for putting texts on before or after the link.
= 1.2 =
New attribute ("target") introduced in Version 1.2. "target" attribute is same as a HTML anchor link target.(Recommend by "Simon")
= 1.3 =
* Link to a post from another blog on a multisite network.

== Usages ==
= Use shortcode "intlink" to put a link. =

= Arguments: =

* blog_id = default 'null'. (Show link from another blog on same network)
* type = default 'post'. (your link type:post/page/cat/author).
* id = default ''. (this is the post/page/author/category id based on the type you have chosen).
* text = default based on the type. Post/Page title for type 'post' or 'page', Category name for 'cat', author display name for 'author'. (the text to show inside the link).
* name = default ''. (Name of the link item object. Ex: "Uncategorized" for category Uncategorized, "Hello world" for the post Hello world).
* slug = default ''. (Slug of the link item object. Ex: "hello-world" for the post Hello world).
* class = default 'w4ils_link'. (A HTML class for the link element "a").
* before = default ''. (Put text or html element before the link).
* after = default ''. (Put text or html element after the link).
* target = default '_parent'. (Same as a HTML anchor link target).