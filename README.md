imgRC
=====

php image resize and cache

Usage
=====

Use it via .htaccess to perform a full transparent cache and editor of images.

copy php files into your application directory, and configure imgRC.php if you want.
Be sure that apache process has write access ti the cacheFolder, to create it and put into all cache files.

.htaccess example
=================
```
Options +FollowSymlinks
RewriteEngine on
RewriteRule		^(.*\.(jpg|png|JPG|PNG|jpeg|JPEG|gif|GIF|pdf\.jpg|pdf\.png))$	imgRC.php?uri=$1	[NC,L,QSA]
```

urls
====

`image.jpg?option=x200,y200,q80,crop,progress,nocache,dureecache,ffpng,gris1,bgff0000`
