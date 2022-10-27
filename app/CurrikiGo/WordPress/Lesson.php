<?php

namespace App\CurrikiGo\WordPress;

use App\Models\Playlist as PlaylistModel;

class Lesson
{
    private $lmsSetting;
    private $client;

    public function __construct($lmsSetting)
    {
        $this->lmsSetting = $lmsSetting;
        $this->client = new \GuzzleHttp\Client();
    }

    public function send(PlaylistModel $playlist, $course_id)
    { 
        // Add Grade level of first activity on project manifest
        $lmsHost = $this->lmsSetting->lms_url;
        $webServiceURL = $lmsHost . "/wp-json/wp/v2/tl_lesson";
        $requestParams = [
            "title" => $playlist->title,
            "status" => "publish",
            "content" => "content",
            'meta' => array(
                'lti_content_id' => $playlist->id,
                'tl_course_id' => $course_id,
                'lti_tool_url' => config('constants.curriki-tsugi-host') . "?playlist=" . $playlist->id,
                'lti_tool_code' => $this->lmsSetting->lti_client_id,
                'lti_custom_attr' =>  'custom=activity='. $playlist->id,
                "lti_content_title" => $playlist->title,
                "lti_post_attr_id" => uniqid()
                
            ),
        ];
        $response = $this->client->request('POST', $webServiceURL, [
        'headers' => [
            'Authorization' => "Basic  " . base64_encode($this->lmsSetting->lms_login_id . ":" . $this->lmsSetting->lms_access_token)
        ],
        'json' => $requestParams
        ]);
        return $response;
    }

    public function update(PlaylistModel $playlist, $course_id, $lesson_id) 
    {        
        $lmsHost = $this->lmsSetting->lms_url;
        $webServiceURL = $lmsHost . "/wp-json/wp/v2/tl_lesson/" . $lesson_id;
        $requestParams = [
            "title" => $playlist->title,
            "status" => "publish",
            "content" => "content",
            'meta' => array(
            'lti_content_id' => $playlist->id,
            'tl_course_id' => $course_id,
            'lti_tool_url' => config('constants.curriki-tsugi-host') . "?playlist=" . $playlist->id,
            'lti_tool_code' => $this->lmsSetting->lti_client_id,
            'lti_custom_attr' =>  'custom=activity='. $playlist->id,
            "lti_content_title" => $playlist->title,
            "lti_post_attr_id" => uniqid()
            ),
        ];
        $response = $this->client->request('POST', $webServiceURL, [
        'headers' => [
            'Authorization' => "Basic  " . base64_encode($this->lmsSetting->lms_login_id . ":" . $this->lmsSetting->lms_access_token)
        ],
        'json' => $requestParams
        ]);
        return $response;
    }

    public function fetch(PlaylistModel $playlist)
    {        
        $lmsHost = $this->lmsSetting->lms_url;
        $webServiceURL = $lmsHost . "/wp-json/wp/v2/tl_lesson?meta_key=lti_content_id&meta_value=". $playlist->id;
        $response = $this->client->request('GET', $webServiceURL);
        return $response;
    }
}
