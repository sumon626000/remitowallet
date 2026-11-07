<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\ContentDetails;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\Upload;

class ContentController extends Controller
{
    use Upload;

    protected $theme;
    protected $useTheme;
    protected $mergedContents;
    protected $languages;
    protected $defaultLang;

    public function __construct()
    {
        $this->theme = basicControl()->theme;
        $this->languages = Language::query()->orderByDesc('default_status')->get();
        $this->defaultLang = $this->languages->firstWhere('default_status', true);

        $this->mergedContents = array_merge(
            config('contents.all', []),
            config("contents.{$this->theme}", [])
        );
    }

    protected function validateAndPrepare($content): array
    {
        if (!array_key_exists($content, (array)$this->mergedContents)) abort(404);

        $this->useTheme = array_key_exists($content, config("contents.{$this->theme}", []))
            ? $this->theme : 'all';

        return [
            'languages' => $this->languages,
            'defaultLanguage' => $this->defaultLang,
            'singleContent' => config("contents.{$this->useTheme}.{$content}.single"),
            'multipleContent' => config("contents.{$this->useTheme}.{$content}.multiple"),
            'contentImage' => config("contents.{$this->useTheme}.{$content}.image"),
        ];
    }

    public function index($content)
    {
        $data = $this->validateAndPrepare($content);
        $multipleContents = $data['multipleContent'];
        $data['singleContentData'] = ContentDetails::with('content')
            ->whereHas('content', fn($q) => $q->where(['name' => $content, 'theme' => $this->useTheme, 'type' => 'single']))
            ->get()->groupBy('language_id');

        $data['multipleContentData'] = ContentDetails::with('content')
            ->whereHas('content', fn($q) => $q->where(['name' => $content, 'theme' => $this->useTheme, 'type' => 'multiple']))
            ->where('language_id', $this->defaultLang->id)
            ->get();

        return view('admin.frontend_management.content.index', $data, compact('content','multipleContents'));
    }

    public function store(Request $request, $content, $language)
    {
        $contentData = $this->validateAndPrepare($content);
        $inputData = $request->except('_token', '_method');

        $validate = Validator::make($inputData, $contentData['singleContent']['validation'] ?? [],
            config('contents.message')
        );

        if ($validate->fails()) {
            $validate->errors()->add('errActive', $language);
            return back()->withInput()->withErrors($validate);
        }

        $singleContent = Content::updateOrCreate(
            ['name' => $content, 'theme' => $this->useTheme],
            ['type' => 'single']
        );

        $mediaData = $this->handleMediaUpload($request, $contentData['singleContent']['size']['image'] ?? null, $inputData, $language, $singleContent);

        if ($mediaData) {
            $singleContent->media = $mediaData;
            $singleContent->save();
        }

        $field_name = array_diff_key($contentData['singleContent']['field_name'] ?? [], config("contents.content_media"));
        $description = $this->prepareDescriptions($inputData, $field_name, $language);

        $contentDetails = ContentDetails::updateOrCreate(
            ['content_id' => $singleContent->id, 'language_id' => $language],
            ['description' => $description]
        );

        return $contentDetails
            ? back()->with('success', 'Content created successfully.')
            : back()->with('error', 'Something went wrong, Please try again.');
    }

    public function manageContentMultiple($content)
    {
        $data = $this->validateAndPrepare($content);
        return view('admin.frontend_management.content.create', $data, compact('content'));
    }

    public function manageContentMultipleStore(Request $request, $content, $language)
    {
        $contentData = $this->validateAndPrepare($content);
        $inputData = $request->except('_token', '_method');

        $validate = Validator::make($inputData, $contentData['multipleContent']['validation'] ?? [],
            config('contents.message')
        );

        if ($validate->fails()) {
            $validate->errors()->add('errActive', $language);
            return back()->withInput()->withErrors($validate);
        }

        $multipleContent = Content::create([
            'name' => $content,
            'theme' => $this->useTheme,
            'type' => 'multiple'
        ]);

        $mediaData = $this->handleMediaUpload($request, $contentData['multipleContent']['size']['image'] ?? null, $inputData, $language, $multipleContent);

        if ($mediaData) {
            $multipleContent->media = $mediaData;
            $multipleContent->save();
        }

        $field_name = array_diff_key($contentData['multipleContent']['field_name'] ?? [], config("contents.content_media"));
        $description = $this->prepareDescriptions($inputData, $field_name, $language);

        $contentDetails = ContentDetails::create([
            'content_id' => $multipleContent->id,
            'language_id' => $language,
            'description' => $description
        ]);

        return $contentDetails
            ? back()->with('success', 'Created Successfully')
            : back()->with('error', 'Something went wrong, Please try again');
    }

    public function multipleContentItemEdit($content, $id)
    {
        $data = $this->validateAndPrepare($content);

        $data['multipleContentData'] = ContentDetails::with('content')
            ->where('content_id', $id)
            ->whereHas('content', fn($q) => $q->where(['name' => $content, 'theme' => $this->useTheme, 'type' => 'multiple']))
            ->get()->groupBy('language_id');

        return view('admin.frontend_management.content.edit', $data, compact('content', 'id'));
    }

    public function multipleContentItemUpdate(Request $request, $content, $id, $language)
    {
        $contentData = $this->validateAndPrepare($content);
        $inputData = $request->except('_token', '_method');

        $validate = Validator::make($inputData, $contentData['multipleContent']['validation'] ?? [],
            config('contents.message')
        );

        if ($validate->fails()) {
            $validate->errors()->add('errActive', $language);
            return back()->withInput()->withErrors($validate);
        }

        $multipleContent = Content::findOrFail($id);

        $mediaData = $this->handleMediaUpload($request, $contentData['multipleContent']['size']['image'] ?? null, $inputData, $language, $multipleContent);

        if ($mediaData) {
            $multipleContent->media = $mediaData;
            $multipleContent->save();
        }

        $field_name = array_diff_key($contentData['multipleContent']['field_name'] ?? [], config("contents.content_media"));
        $description = $this->prepareDescriptions($inputData, $field_name, $language);

        $contentDetails = ContentDetails::updateOrCreate(
            ['content_id' => $id, 'language_id' => $language],
            ['description' => $description]
        );

        return $contentDetails
            ? back()->with('success', 'Updated Successfully')
            : back()->with('error', 'Something went wrong, Please try again');
    }

    public function ContentDelete($id)
    {
        try {
            $content = Content::findOrFail($id);
            $this->fileDelete(optional($content->media->image)->driver ?? null, optional($content->media->image)->path ?? null);

            ContentDetails::where('content_id', $id)->delete();
            $content->delete();

            return back()->with('success', 'Content has been deleted');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function handleMediaUpload($request, $size, $inputData, $language, $content)
    {
        $mediaData = [];

        foreach (config('contents.content_media') as $key => $media) {
            $old = $content->media->{$key} ?? null;

            if ($request->hasFile($key)) {
                $mediaData[$key] = $this->fileUpload($request->$key, config('filelocation.contents.path'),
                    null,
                    $size,
                    'webp',
                    80,
                    optional($old)->path,
                    optional($old)->driver
                );
            } elseif ($request->has($key)) {
                $mediaData[$key] = $inputData[$key][$language];
            } elseif ($old) {
                $mediaData[$key] = $old;
            }
        }

        return $mediaData;
    }

    private function prepareDescriptions($inputData, $field_name, $language)
    {
        $description = [];
        foreach ($field_name as $name => $type) {
            $description[$name] = $inputData[$name][$language];
        }
        return $description;
    }
}
