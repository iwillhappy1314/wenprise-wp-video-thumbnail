<?php

/*
 * Video thumbnail class for implementing FFMpeg Features
 */

namespace Wenprise\VideoThumbnail;

use Exception;
use FFMpeg\Coordinate;
use FFMpeg\FFMpeg;

/**
 * @author     sukhilss <emailtosukhil@gmail.com>
 * @package    Video Thumbnail
 * @version    1.0.0
 */
class VideoThumbnail
{

    /**
     * @var null
     */
    protected $FFMpeg = null;


    /**
     * @var null
     */
    protected $videoObject = null;

    /**
     * @var null
     */
    protected $videoURL = null;

    /**
     * @var null
     */
    protected $storageURL = null;

    /**
     * @var null
     */
    protected $thumbName = null;

    /**
     * @var null
     */
    public $fullFile = null;

    /**
     * @var int
     */
    public $height = 240;

    /**
     * @var int
     */
    public $width = 320;

    /**
     * @var int
     */
    public $screenShotTime = 1;


    /**
     * VideoThumbnail constructor.
     *
     * @param $ffmpeg_path
     * @param $ffprobe_path
     */
    public function __construct($ffmpeg_path, $ffprobe_path)
    {
        $this->FFMpeg = FFMpeg::create([
            'ffmpeg.binaries'  => $ffmpeg_path,
            'ffprobe.binaries' => $ffprobe_path,
        ]);
    }


    /**
     * 创建缩略图
     *
     * @param     $videoUrl
     * @param     $storageUrl
     * @param     $fileName
     * @param     $second
     * @param int $width
     * @param int $height
     *
     * @return $this
     */
    public function createThumbnail($videoUrl, $storageUrl, $fileName, $second, $width = 640, $height = 480)
    {
        $this->videoURL = $videoUrl;

        $this->storageURL = $storageUrl;
        $this->thumbName  = $fileName;
        $this->fullFile   = "{$this->storageURL}/{$this->thumbName}";

        $this->screenShotTime = $second;

        $this->width  = $width;
        $this->height = $height;

        try {
            $this->create();
            $this->thumbnail();

            $image = wp_get_image_editor($this->fullFile);

            if ( ! is_wp_error($image)) {
                $image->resize($this->width, $this->height, false);
                $image->save($this->fullFile);
            }

        } catch (Exception $e) {
            error_log($e->getMessage());
        }

        return $this;
    }


    /**
     * 添加水印
     *
     * @param $overlayPath
     * @param $name
     */
    public function overlay($overlayPath, $name)
    {
        try {
            $water_mark = "{$overlayPath}/{$name}";

            $src = imagecreatefrompng($water_mark);
            $tmp = imagecreatefromjpeg($this->fullFile);

            // Set the brush
            imagesetbrush($tmp, $src);

            // Draw a couple of brushes, each overlaying each
            imageline($tmp, imagesx($tmp) / 2, imagesy($tmp) / 2, imagesx($tmp) / 2, imagesy($tmp) / 2, IMG_COLOR_BRUSHED);
            imagejpeg($tmp, $this->fullFile, 100);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }


    /**
     * 生成 FFmpeg 对象
     *
     * @return \FFMpeg\Media\Audio|\FFMpeg\Media\Video
     */
    private function create()
    {
        $this->videoObject = $this->FFMpeg->open($this->videoURL);

        return $this->videoObject;
    }


    /**
     * 生成缩略图
     *
     * @return null
     */
    private function resize()
    {
        $this->videoObject
            ->filters()
            ->resize(new Coordinate\Dimension($this->width, $this->height))
            ->synchronize();

        return $this->videoObject;
    }


    /**
     * 获取一帧作为缩略图
     *
     * @return null
     */
    private function thumbnail()
    {
        $this->videoObject->frame(Coordinate\TimeCode::fromSeconds($this->screenShotTime))->save($this->fullFile);

        return $this->videoObject;
    }

}
