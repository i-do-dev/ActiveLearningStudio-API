<?php

namespace App\CurrikiGo\WordPress;

use App\Models\Playlist as PlaylistModel;
use App\Models\Project;
class Lesson
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

    public function send(PlaylistModel $playlist, $course_id, $data)
    { 
        // Add Grade level of first activity on project manifest
        $lmsHost = $this->lmsSetting->lms_url;
        $webServiceURL = $lmsHost . "/wp-json/wp/v2/tl_lesson";
        $requestParams = [
            "title" => $playlist->title . ($data['counter'] > 0 ? ' (' .$data['counter'] . ')' : ''),
            "status" => "publish",
            "content" => "content",
            'meta' => array(
                'lti_content_id' => $playlist->id,
                'tl_course_id' => $course_id,
                'lti_tool_url' => config('constants.curriki-tsugi-host') . "?playlist=" . $playlist->id,
                'lti_tool_code' => $this->lmsSetting->lti_client_id,
                'lti_custom_attr' =>  'custom=activity='. $playlist->id,
                "lti_content_title" => $playlist->title . ($data['counter'] > 0 ? ' (' .$data['counter'] . ')' : ''),
                "lti_post_attr_id" => uniqid(),
                "lti_course_id" =>  $playlist->project->id
            ),
        ];
        $response = $this->client->request('POST', $webServiceURL, [
        'headers' => [
            'Authorization' => "Basic  " . $this->lmsAuthToken 
        ],
        'json' => $requestParams
        ]);
        return $response;
    }

    public function fetch(Project $project)
    {
        $lmsHost = $this->lmsSetting->lms_url;
        $webServiceURL = $lmsHost . "/wp-json/wp/v2/tl_lesson?meta_key=lti_course_id&meta_value=". $project->id;
        $response = $this->client->request('GET', $webServiceURL, [
            'headers' => [
                'Authorization' => "Basic  " . $this->lmsAuthToken
            ]
        ]);
        return $response;
    }
}
