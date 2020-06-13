<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use App\Rules\hasGenre;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VideoController extends BasicCrudController
{
    private $rules;

    public function __construct()
    {
        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y',
            'opened' => 'boolean',
            'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration' => 'required|integer',
            'category_id' => 'required|array|exists:categories,id',
            'genre_id' => 'required|array|exists:genres,id'
        ];
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rolesStore());
        $self = $this;
        /** @var Video $obj **/
       $obj = \DB::transaction(function () use($request, $validatedData, $self) {
            $obj = $this->model()::create($validatedData);
            $self->handleRelations($obj, $request);
            return $obj;
       });
        $obj->refresh();
        return $obj;
    }

    public function update(Request $request, $id)
    {
        $validatedData = $this->validate($request, $this->rolesUpdate());
        $self = $this;
        $obj = \DB::transaction(function () use($request, $validatedData, $id, $self) {
            $obj = $this->findOrFail($id);
            $obj->update($validatedData);
            $self->handleRelations($obj, $request);
            return $obj;
        });
        $obj->refresh();
        return $obj;
    }

    protected function handleRelations($video, Request $request)
    {
        $video->categories()->sync($request->get('categories_id'));
        $video->genres()->sync($request->get('genre_id'));
    }

    protected function model()
    {
        return Video::class;
    }
    protected function rolesStore()
    {
        return $this->rules;
    }
    protected function rolesUpdate()
    {
        return $this->rules;
    }
}
