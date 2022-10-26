<?php

namespace App\CurrikiGo\WordPress;

use App\Models\Playlist as PlaylistModel;

class Course
{
    private $lmsSetting;
    private $client;

    public function __construct($lmsSetting)
    {
        $this->lmsSetting = $lmsSetting;
        $this->client = new \GuzzleHttp\Client();
    }

    public function send(PlaylistModel $playlist)
    {        
        $lmsHost = $this->lmsSetting->lms_url;
        $webServiceURL = $lmsHost . "/wp-json/wp/v2/tl_courses";
        $requestParams = [
            "title" => $playlist->project->name,
            "status" => "publish",
            "content" => "content",
            'meta' => array('lti_content_id' => $playlist->project->id)
        ];
        $response = $this->client->request('POST', $webServiceURL, [
        'headers' => [
            'Authorization' => "Basic  " . base64_encode($this->lmsSetting->lms_login_id . ":" . $this->lmsSetting->lms_access_token)
        ],
        'json' => $requestParams
        ]);
        return $response;
    }

    public function update(PlaylistModel $playlist, $courseId)
    {        
        $lmsHost = $this->lmsSetting->lms_url;
        $webServiceURL = $lmsHost . "/wp-json/wp/v2/tl_courses/" . $courseId;
        $requestParams = [
            "title" => $playlist->project->name,
            "status" => "publish",
            "content" => "content",
            'meta' => array('lti_content_id' => $playlist->project->id)
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
        $webServiceURL = $lmsHost . "/wp-json/wp/v2/tl_courses?meta_key=lti_content_id&meta_value=". $playlist->project->id;
        $response = $this->client->request('GET', $webServiceURL);
        return $response;
    }



}
