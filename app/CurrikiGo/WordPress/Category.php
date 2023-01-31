<?php

namespace App\CurrikiGo\WordPress;

use App\Models\Playlist as PlaylistModel;
use App\Models\Organization;

class Category
{
    private $lmsSetting;
    private $client;
    private $lmsAuthToken;
    private $type;

    public function __construct($lmsSetting, $type)
    {
        $this->lmsSetting = $lmsSetting;
        $this->client = new \GuzzleHttp\Client();
        $this->lmsAuthToken = base64_encode($lmsSetting->lms_login_id . ":" . $lmsSetting->lms_access_token);
        $this->type = $type;
    }

    public function send($tagName, $parentId = 0)
    {
        $lmsHost = $this->lmsSetting->lms_url;
        $webServiceURL = $lmsHost . "/wp-json/wp/v2/tl_" . $this->type . "_category?parent=" . $parentId;
        $requestParams = [
            "name" => $tagName,
            "taxonomy" => "tl_" . $this->type . "_category"
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
        $webServiceURL = $lmsHost .  "/wp-json/wp/v2/tl_" . $this->type . "_category/?per_page=100";
        $response = $this->client->request('GET', $webServiceURL, [
            'headers' => [
                'Authorization' => "Basic  " . $this->lmsAuthToken
            ]
        ]);
        return $response;
    }

    public function getOrganizationTree($organizationId, $tree = [], $index = 0)
    {
        $organization = Organization::find($organizationId);
        $tree[$index]['name'] = $organization->name;
        $tree[$index]['id'] = $organization->id;
        $tree[$index]['parent_id'] = $organization->parent_id;
        $index++;
        if (gettype($organization->parent_id) == "integer") {
            return $this->getOrganizationTree($organization->parent_id, $tree, $index);
        } else {
            return $tree;
        }
    }

    public function syncOrganizations($organizationTree, $existingCategoriesWP, $organization)
    {

        $existingCategories = $tagIds = [];
        $index = 0;
        foreach ($existingCategoriesWP as $tag) {
            $existingCategories[$index]['name'] = trim(html_entity_decode($tag->name));
            $existingCategories[$index]['tagId'] = $tag->id;
            $existingCategories[$index]['parentId'] = $tag->parent;
            $index++;
        }
        $parentIndex = 0;
        foreach ($organizationTree as $key => $tree) {
            $matchedIndex = array_search(trim($tree['name']), array_column($existingCategories, 'name'));
            if (gettype($matchedIndex) != "integer") {
                if ($tree['parent_id']) {
                    $response = $this->send($tree['name'],  $existingCategories[$parentIndex]['tagId']);
                } else {
                    $response = $this->send($tree['name'], 0);
                }
                $tag = $response->getBody()->getContents();
                $tag = json_decode($tag);
                $existingCategories[$index]['tagId'] =  $tag->id;
                $existingCategories[$index]['name'] = trim($tree['name']);
                $existingCategories[$index]['parentId'] =  $tag->parent;
                array_push($tagIds, $tag->id);
                array_unique($tagIds);
                $parentIndex = $index;
                $index++;
            } else {
                $parentIndex =  $matchedIndex;
            }
        }
        $matchedIndex = array_search(trim($organization->name), array_column($existingCategories, 'name'));
        $matchedOrganization =  $existingCategories[$matchedIndex]['tagId'];
        return  $matchedOrganization;
    }


}
