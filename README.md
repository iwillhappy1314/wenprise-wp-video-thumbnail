## Video Thumbnail

Simple package for generating video thumbnail in WordPress 5 using FFMpeg.

## Installation

Composer install:

```
composer require wenprise/video-thumbnail
```


```
$video = new Wenprise\VideoThumbnail\VideoThumbnail('/usr/bin/ffmpeg', '/usr/bin/ffprobe');

$thumbnail = $video->createThumbnail(get_theme_file_path('demo.mp4'), get_theme_file_path(), 'thumbnail.png', 1, 150, 100);
```