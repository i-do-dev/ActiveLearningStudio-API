<?php

namespace App\CurrikiGo\WordPress;

use App\Models\Playlist as PlaylistModel;

class Course
{
    private $lmsSetting;
    private $client;
    private $lmsAuthToken;

    public function __construct($lmsSetting)
    {
        $this->lmsSetting = $lmsSetting;
        $this->client = new \GuzzleHttp\Client();
        $this->lmsAuthToken = base64_encode($lmsSetting->lms_login_id . ":" . $lmsSetting->lms_access_token);
    }

    public function send(PlaylistModel $playlist, $tagsArray)
    {        
        $lmsHost = $this->lmsSetting->lms_url;
        $webServiceURL = $lmsHost . "/wp-json/wp/v2/tl_courses"; // web service endpoint
        $requestParams = [
            "title" => $playlist->project->name,
            "status" => "publish",
            'meta' => array('lti_content_id' => $playlist->project->id),
            'tl_course_tag' =>  $tagsArray
        ];
        $response = $this->client->request('POST', $webServiceURL, [
        'headers' => [
            'Authorization' => "Basic  " . $this->lmsAuthToken
        ],
        'json' => $requestParams
        ]);
        return $response;
    }

    public function update(PlaylistModel $playlist, $courseId ,$tagsArray)
    {        
        $lmsHost = $this->lmsSetting->lms_url;
        $webServiceURL = $lmsHost . "/wp-json/wp/v2/tl_courses/" . $courseId;
        $requestParams = [
            "title" => $playlist->project->name,
            "status" => "publish",
            'meta' => array('lti_content_id' => $playlist->project->id),
            'tl_course_tag' =>  $tagsArray
        ];
        $response = $this->client->request('POST', $webServiceURL, [
        'headers' => [
            'Authorization' => "Basic  " . $this->lmsAuthToken
        ],
        'json' => $requestParams
        ]);
        return $response;
    }

    public function fetch(PlaylistModel $playlist)
    {        
        $lmsHost = $this->lmsSetting->lms_url;
        $webServiceURL = $lmsHost . "/wp-json/wp/v2/tl_courses?meta_key=lti_content_id&meta_value=". $playlist->project->id;
        $response = $this->client->request('GET', $webServiceURL, [
            'headers' => [
                'Authorization' => "Basic  " . $this->lmsAuthToken
            ]
        ]);
        return $response;
    }
}
