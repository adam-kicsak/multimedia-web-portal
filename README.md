# multimedia-web-portal
The source code of my Business Informatics BSc thesis, 2011

The main goal of the portal is to create a multimedia web portal that takes the advantages of the new web technologies in 2011 such as HTML5, CSS3 and the new JS APIs.

The portal uses the \<video> and the \<audio> tags that were the main new features of the HTML5 in 2011. The portal can play multimedia content whitout browser plugins.

## Development environment
1. OS: Windows XP
1. Web server: Apache 2.2
1. Server side scripting: PHP 5.3.6
1. Database server: MySQL 5.5
1. Multimedia file converter: FFmpeg 6.3.0 (tested with this version in May 2017)

## Configuration

All of the configuration files are attached that differ from the default. The configuration results a pendrive portable server environment. 

The directory structure:
```
[Pendrive Root]
+- Webserver
   +- apache
   +- mwp [Multimedia Web Portal scripts and resources]
   +- mysql
   +- php
   +- temp
      +- fupld [Temporary file upload]
      +- sessn [PHP sessions]
```

The ffmpeg.exe must be on the PATH or must be copied into \Webserver\mwp\Converter directory.
