# SFTube

![Screenshot](https://github.com/dimitrios-kourkoumpas/sftube.net/blob/development/docs/screenshots/1%20-%20website/1%20-%20homepage.png)

This is a personal project built for demonstration purposes.

It showcases a simple website where users can upload short videos, comment, vote and search.

It also features an administration panel and a REST API for all of the above.

Videos are processed asynchronously after upload with the help of the [PHP-FFMpeg](https://github.com/PHP-FFMpeg/PHP-FFMpeg) library and presented in the homepage grid
with their respective thumbnails.

There are two strategies for [processing the videos](https://github.com/dimitrios-kourkoumpas/sftube.net/tree/development/src/Service/VideoExtractor)
* Slideshow extraction
  * frames are extracted as images and then presented as a cycling slideshow upon mouse-hover in the homepage grid
* Preview extraction
  * short video clips are extracted at defined time intervals and then merged into a preview clip that plays automatically upon mouse-hover in the homepage grid.



### Technologies used

* PHP 8.2
* Symfony 6.3
  * API Platform
  * Mercure
* MariaDB
* ElasticSearch
* jQuery
  * Cycle2
  * Datatables (AJAX)

### Other packages

* PHP-FFMpeg
* RabbitMQ
  * Symfony Messenger
* Vich Uploader
* FOSElasticaBundle
* SncRedisBundle

Screenshots can be found [here](https://github.com/dimitrios-kourkoumpas/sftube.net/tree/development/docs/screenshots)

### License
Distributed under the MIT License.