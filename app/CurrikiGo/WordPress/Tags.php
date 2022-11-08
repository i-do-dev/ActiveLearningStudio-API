<?php

namespace App\CurrikiGo\WordPress;

use App\Models\Playlist as PlaylistModel;
use App\Models\Activity as ActivityModel;

class Tags
{
    private $lmsSetting;
    private $client;
    private $lmsAuthToken;
    private $existingTagArrayIndex;
    private $type;
    private $existingTags;

    public function __construct($lmsSetting, $type)
    {
        $this->lmsSetting = $lmsSetting;
        $this->client = new \GuzzleHttp\Client();
        $this->lmsAuthToken = base64_encode($lmsSetting->lms_login_id . ":" . $lmsSetting->lms_access_token);
        $this->existingTagArrayIndex = 0;
        $this->type = $type;
        $this->existingTags = [];
    }

    public function send($tagName)
    {
        $lmsHost = $this->lmsSetting->lms_url;
        $webServiceURL = $lmsHost . "/wp-json/wp/v2/tl_" . $this->type . "_tag";
        $requestParams = [
            "name" => $tagName,
            "taxonomy" => "tl_" . $this->type . "_tag"
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
        $webServiceURL = $lmsHost .  "/wp-json/wp/v2/tl_" . $this->type . "_tag/?per_page=100";
        $response = $this->client->request('GET', $webServiceURL, [
            'headers' => [
                'Authorization' => "Basic  " . $this->lmsAuthToken
            ]
        ]);
        return $response;
    }

    public function returnIds(PlaylistModel $playlist, $tags)
    {
        $tagIds = [];
        foreach ($tags as $tag) {
            $this->existingTags[$this->existingTagArrayIndex]['name'] = trim(html_entity_decode($tag->name));
            $this->existingTags[$this->existingTagArrayIndex]['tagId'] = $tag->id;
            $this->existingTagArrayIndex++;
        }
        $tagIds = $this->activityTags($playlist, $tagIds, 'subjects');
        $tagIds = $this->activityTags($playlist, $tagIds, 'authorTags');
        $tagIds = $this->activityTags($playlist, $tagIds, 'educationLevels');

        return $tagIds;
    }

    private function activityTags($playlist, $tagIds, $category)
    {
        $activities = ActivityModel::where('playlist_id', $playlist->id)->with($category)->get();
        foreach ($activities as $activity) {
            foreach ($activity->$category as $item) {
                $item->name = $item->name;
                $index = array_search(trim($item->name), array_column($this->existingTags, 'name'));
                if (gettype($index) != "integer") {
                    $response = $this->send($item->name);
                    $tag = $response->getBody()->getContents();
                    $tag = json_decode($tag);
                    $this->existingTags[$this->existingTagArrayIndex]['tagId'] =  $tag->id;
                    $this->existingTags[$this->existingTagArrayIndex]['name'] = trim($item->name);
                    array_push($tagIds, $tag->id);
                    array_unique($tagIds);
                    $this->existingTagArrayIndex++;
                } else {
                    array_push($tagIds, $this->existingTags[$index]['tagId']);
                    array_unique($tagIds);
                }
            }
            if ($this->type == "course" && !empty($tagIds)) {
                break;
            }
        }
        return $tagIds;
    }
}
