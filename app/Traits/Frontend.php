<?php

namespace App\Traits;

use App\Models\Blog;
use App\Models\ContentDetails;
use App\Models\Gateway;
use App\Models\User;

trait Frontend
{
    protected function getSectionsData($sections, $content, $selectedTheme)
    {
        if ($sections == null) {
            $data = ['support' => $content,];
            return view("themes.$selectedTheme.support", $data)->toHtml();
        }

        $contentData = ContentDetails::with('content')
            ->whereHas('content', function ($query) use ($sections,$selectedTheme) {
                $query->whereIn('name', $sections)->where('theme', $selectedTheme)->orWhere('theme','all');
            })
            ->get();

        foreach ($sections as $section) {
            $singleContent = $contentData->where('content.name', $section)->where('content.type', 'single')->first() ?? [];

            if ($section == 'blog') {
                $data[$section] = [
                    'single' => $singleContent ? collect($singleContent->description ?? [])->merge($singleContent->content->only('media')) : [],
                    'multiple' => Blog::query()
                        ->where('status',1)
                        ->with('details')
                        ->limit(3)
                        ->get()
                ];
            }
            elseif ($section == 'gateway' && getTheme() == 'rivo') {
                $data[$section] = [
                    'single' => $singleContent ? collect($singleContent->description ?? [])->merge($singleContent->content->only('media')) : [],
                    'multiple' => Gateway::query()
                        ->where('status', 1)
                        ->whereNotNull('image')
                        ->select('driver', 'image')
                        ->get()
                        ->map(function ($gateway) {
                            return getFile($gateway->driver, $gateway->image);
                        })
                        ->toArray()
                ];
            }

            else {
                $multipleContents = $contentData->where('content.name', $section)
                    ->where('content.type', 'multiple')
                    ->values()
                    ->map(function ($multipleContentData) {
                        return collect($multipleContentData->description)->merge($multipleContentData->content->only('media'));
                    });

                $media = $singleContent->content->media ?? null;
                $mediaFile = [];
                if ($media && is_object($media)) {
                    foreach ($media as $key => $value) {
                        if (is_object($value) && str_contains($key, 'image') && isset($value->driver, $value->path)) {
                            $mediaFile[$key] = getFile($value->driver, $value->path);
                        } else {
                            $mediaFile[$key] = $value;
                        }
                    }
                } else {
                    $mediaFile = null;
                }

                $data[$section] = [
                    'single' => $singleContent
                        ? collect($singleContent->description ?? [])->merge($singleContent->content->only('media'))
                        : [],
                    'multiple' => $multipleContents,
                    'mediaFile' => (object) $mediaFile,
                ];

                if ($section === 'hero' && getTheme() == 'nova') {
                    $data[$section]['users'] = User::query()
                        ->select(['id', 'image', 'image_driver'])
                        ->where('status', 1)
                        ->limit(4)
                        ->get()
                        ->map(function ($user) {
                            $user->imagelink = getFile($user->image_driver, $user->image);
                            return $user;
                        });
                }
                if ($section == 'gateway' && getTheme() == 'nova') {
                    $data[$section]['multiple'] = Gateway::query()
                        ->where('status', 1)
                        ->whereNotNull('image')
                        ->select('driver', 'image')
                        ->get()
                        ->map(function ($gateway) {
                            return getFile($gateway->driver, $gateway->image);
                        })
                        ->toArray();
                }


            }


            $replacement = view("themes.$selectedTheme.sections.{$section}", $data)->toHtml();

            $content = str_replace('<div class="custom-block" contenteditable="false"><div class="custom-block-content">[[' . $section . ']]</div>', $replacement, $content);
            $content = str_replace('<span class="delete-block">×</span>', '', $content);
            $content = str_replace('<span class="up-block">↑</span>', '', $content);
            $content = str_replace('<span class="down-block">↓</span></div>', '', $content);
            $content = str_replace('<p><br></p>', '', $content);
        }

        return $content;
    }

    protected function handleDatabaseException(\Exception $exception)
    {
        switch ($exception->getCode()) {
            case 404:
                abort(404);
            case 403:
                abort(403);
            case 401:
                abort(401);
            case 503:
                redirect()->route('maintenance')->send();
                break;
            case "42S02":
                die($exception->getMessage());
            case 1045:
                die("Access denied. Please check your username and password.");
            case 1044:
                die("Access denied to the database. Ensure your user has the necessary permissions.");
            case 1049:
                die("Unknown database. Please verify the database name exists and is spelled correctly.");
            case 2002:
                die("Unable to connect to the MySQL server. Check the database host and ensure the server is running.");
//            case 0:
//                die("Unknown connection issue. Verify your connection parameters and server status.");
            default:
                redirect()->route('instructionPage')->send();
        }
    }


}
