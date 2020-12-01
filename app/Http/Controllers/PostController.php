<?php

namespace App\Http\Controllers;

use App\Helpers\Transformer;
use App\Http\Resources\CommentsCollection;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostsCollection;
use App\Models\Post;
use App\Models\Screenshot;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Post not found response.
     *
     * @return  JsonResponse
     */
    private function postNotFoundResponse()
    {
        return Transformer::fail('Post not found.', null, 404);
    }

    /**
     * Get Posts collection.
     *
     * @param   Request $request
     *
     * @return  JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $posts = Post::select('id', 'user_id', 'title', 'solved', 'views', 'created_at', 'updated_at')
                            ->with(['author:id,avatar,username', 'screenshots'])
                            ->when($request->has('search'), function ($query) use ($request) {
                                $query->where('title', 'like', "%{$request->get('search')}%");
                            })
                            ->when($request->has('filter'), function ($query) use ($request) {
                                switch (strtolower($request->get('filter'))) {
                                    case 'solved':
                                        $query->where('solved', 'Y');
                                        break;
                                    
                                    case 'unsolved':
                                        $query->where('solved', 'N');
                                        break;
                                }
                            })
                            ->orderBy('created_at')
                            ->paginate(10);

            return (new PostsCollection($posts))
                            ->additional(Transformer::meta(true, 'Success to get posts collection.'));
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get posts collection.');
        }
    }

    /**
     * Get post's comments collection.
     *
     * @param   Request  $request
     * @param   string   $id
     *
     * @return  JsonResponse
     */
    public function getComments(Request $request, $id)
    {
        try {
            $post = Post::select('id')->whereId($id)->firstOrFail();
            $comments = $post->comments()
                                ->when($request->has('sort'), function ($query) use ($request) {
                                    switch (strtolower($request->get('sort'))) {
                                        // sort: old,new
                                        case 'old':
                                            $query->orderByDesc('created_at');
                                            break;
                                        
                                        default:
                                            $query->orderBy('created_at');
                                            break;
                                    }
                                })
                                ->paginate(10);

            return (new CommentsCollection($comments))
                        ->additional(
                            Transformer::meta(true, 'Success to get post\' comments.'),
                        );
        } catch (ModelNotFoundException $th) {
            return Transformer::modelNotFound('Post');
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get post\' comments.');
        }
    }

    /**
     * Store screenshot image.
     *
     * @param   UploadedFile  $screenshot
     *
     * @return  string
     */
    private function storeScreenshot(UploadedFile $screenshot)
    {
        $destination = base_path('public/' . Post::$screenshots_folder);
        $ext = $screenshot->getClientOriginalExtension();
        $final_file_name = uniqid(rand()) . '.' . $ext;

        $screenshot->move($destination, $final_file_name);

        return Post::$screenshots_folder . '/' . $final_file_name;
    }

    /**
     * Remove screenshot from storage.
     *
     * @param   string  $screenshot
     *
     * @return  bool
     */
    private function removeScreenshot(string $screenshot)
    {
        return unlink(base_path('public/' . $screenshot));
    }

    /**
     * Attach tags to post
     *
     * @param   Post   $post
     * @param   array  $tags
     *
     * @return  void
     */
    public function attachTags(Post $post, array $tags)
    {
        $tags_data = collect($tags)->map(function (string $tag) {
            return [
                'name' => strtolower($tag),
            ];
        });

        $post->tags()->createMany($tags_data);
    }

    /**
     * Create new post.
     *
     * @param   Request  $request
     *
     * @return  JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'tags' => 'required|array',
            'tags.*' => 'string|regex:/^[a-zA-Z]+$/',
            'screenshots' => 'nullable|array',
            'screenshots.*' => 'image|mimes:png,jpeg|max:4096',
        ]);

        try {
            // Create post.
            $post = Post::create(array_merge(
                [
                    'id' => Post::generateUuid(),
                    'user_id' => Auth::id(),
                ],
                $request->only('title', 'body'),
            ));

            // Store screenshots
            if (($request->hasFile('screenshots'))) {
                // Store screenshots into storage
                $screenshot_names = [];
                foreach ($request->file('screenshots') as $screenshot) {
                    array_push($screenshot_names, [
                        'image' => $this->storeScreenshot($screenshot),
                    ]);
                }

                // Store to DB
                if (count($screenshot_names) > 0) {
                    $post->screenshots()->createMany($screenshot_names);
                }
            }

            // Attach tags
            $this->attachTags($post, $request->get('tags'));

            return Transformer::ok('Success to create post.', new PostResource($post), 201);
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to create post.');
        }
    }
    

    /**
     * Update post data.
     *
     * @param   Request $request
     * @param   int     $id
     *
     * @return  JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'tags' => 'required|array',
            'tags.*' => 'string|regex:/^[a-zA-Z]+$/',
            'screenshots' => 'nullable|array',
            'screenshots.*' => 'image|mimes:jpg,jpeg,png|max:4096',
            'old_screenshots' => 'nullable|array',
            'old_screenshots.*' => 'string',
        ]);

        try {
            $post = Post::select('id', 'user_id', 'views')->whereId($id)->firstOrFail();
            
            $this->authorize('update', $post);

            // Update post
            $post->update($request->only('title', 'body'));

            // Check & delete old screenshots
            if ($post->screenshots->count() > 0) {
                $old_screenshots = $request->get('old_screenshots');

                // Get the unused screenshots
                $delete_able_screenshots = [];
                if ((!is_array($old_screenshots) && is_null($old_screenshots)) || count($old_screenshots) == 0) {
                    $delete_able_screenshots = $post->screenshots->map(function ($screenshot) {
                        return $screenshot->image;
                    })->toArray();
                } else {
                    foreach ($post->screenshots as $screenshot) {
                        if (!in_array($screenshot->image, $old_screenshots)) {
                            array_push($delete_able_screenshots, $screenshot->image);
                        }
                    }
                }

                // Remove old screenshots
                if (count($delete_able_screenshots) > 0) {
                    // Remove from Storage
                    foreach ($delete_able_screenshots as $delete_able_screenshot) {
                        $this->removeScreenshot($delete_able_screenshot);
                    }
                
                    // Remove from DB
                    $post->screenshots()->whereIn('image', $delete_able_screenshots)->delete();
                }
            }

            // Store screenshots
            if (($request->hasFile('screenshots'))) {
                // Store screenshots into storage
                $screenshot_names = [];
                foreach ($request->file('screenshots') as $screenshot) {
                    array_push($screenshot_names, [
                        'image' => $this->storeScreenshot($screenshot),
                    ]);
                }

                // Store to DB
                if (count($screenshot_names) > 0) {
                    $post->screenshots()->createMany($screenshot_names);
                }
            }

            // Detach all tags
            $post->tags()->delete();

            // Store Tags
            $this->attachTags($post, $request->get('tags'));

            // Refresh model
            $post->refresh();

            return Transformer::ok('Success to update post.', new PostResource($post));
        } catch (ModelNotFoundException $th) {
            return $this->postNotFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to update post.');
        }
    }

    /**
     * Increment post views.
     *
     * @param   int  $id
     *
     * @return  JsonResponse
     */
    public function incrementViews($id)
    {
        try {
            $post = Post::select('id', 'views')->whereId($id)->firstOrFail();
            $post->increment('views');

            return Transformer::ok('Success to update post views.');
        } catch (ModelNotFoundException $th) {
            return $this->postNotFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to update post views.');
        }
    }

    /**
     * Mark post solved.
     *
     * @param   int  $id
     *
     * @return  JsonResponse
     */
    public function markSolved($id)
    {
        try {
            $post = Post::select('id', 'user_id')->whereId($id)->firstOrFail();

            $this->authorize('update', $post);

            $post->update([
                'solved' => 'Y'
            ]);

            return Transformer::ok('Success to update post data.');
        } catch (ModelNotFoundException $th) {
            return $this->postNotFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to update post data.');
        }
    }

    /**
    * Increment post views.
    *
    * @param   int  $id
    *
    * @return  JsonResponse
    */
    public function destroy($id)
    {
        try {
            $post = Post::select('id', 'user_id')->whereId($id)->firstOrFail();

            $this->authorize('delete', $post);

            // Delete screenshots
            $post->screenshots->each(function (Screenshot $screenshot) {
                $this->removeScreenshot($screenshot->image);
            });

            // Delete model
            $post->delete();

            return Transformer::ok('Success to delete post data.');
        } catch (ModelNotFoundException $th) {
            return $this->postNotFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to delete post data.');
        }
    }
}
