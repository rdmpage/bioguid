On Snow Leopard Server need to create
```
 /usr/local
 /usr/local/bin
 /usr/local/include
 /usr/local/lib
```
## MySQL ##

In order to build bigram I need mysql headers, etc., so ended up installing fresh copy of MySQL, so now have to versions. The one I installed is at

/usr/local/mysql/bin/mysql

typing "mysql" at command prompt start's Apple's, so need

/usr/local/mysql/bin/mysql

### bigram ###

I use bigram indexing to find matching journal titles (see http://iphylo.blogspot.com/2009/10/n-gram-fulltext-indexing-in-mysql.html). Initially I used hhttp://sourceforge.net/projects/mysqlftppc/, but this wouldn't build on Sbow Leopard, so I use http://sites.google.com/site/mysqlbigram/ instead.

mysqlftppc needs ICU

### ICU ###
```
cd icu/source
./configure
make
sudo make install
```

## ImageMagick ##

We use this to generate image thumbnails. Need to get JPEG, TIFF, PNG,
GhostScript (for PDF thumbnails), and FreeType before building.


### JPEG library ###
```
cd jpeg-6b
./configure
make
sudo make install-lib
```

### PNG library ###
```
cd libpng-1.2.16
./configure
make
sudo make install
```

### TIFF library ###
```
cd tiff-3.8.2
./configure
make
sudo make install
```

### GhostScript ###
```
cd ghostscript-8.64
make
sudo make install
```

### ImageMagick ###
```
cd ImageMagick-6.5.8-6
./configure
make
sudo make install
```

## PDF ##
We will need PDF support, especially text extraction

### FreeType ###

2.2.1 won't build on Snow Leopard, use 2.3.11

```
cd freetype-2.3.11
./configure
make
sudo make install
```

### xpdf ###

Need to tell it explicitly where to find FreeType
```
cd xpdf-3.02
./configure --with-freetype2-includes=/usr/local/include/freetype2/
make
sudo make install
```