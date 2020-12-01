<?php

namespace App\Http\Controllers;

use App\Helpers\Transformer;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Create new comment.
     *
     * @param   Request  $request
     *
     * @return  JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            '_post_id' => 'required|string',
            'body' => 'required|string',
        ]);

        try {
            // Get the post model
            $post = Post::select('id')->whereId($request->get('_post_id'))->firstOrFail();

            // Create comment
            $comment = $post->comments()->create([
                'user_id' => Auth::id(),
                'body' => $request->get('body'),
            ]);

            return Transformer::ok('Success to create comment.', new CommentResource($comment), 201);
        } catch (ModelNotFoundException $th) {
            return Transformer::fail('Post not found.', null, 404);
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to create comment.');
        }
    }

    /**
     * Update comment data.
     *
     * @param   Request         $request
     * @param   int|string      $id
     *
     * @return  JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'body' => 'required|string'
        ]);

        try {
            // Get comment model
            $comment = Comment::findOrFail($id);

            // Authorize user
            $this->authorize('update', $comment);

            // Update comment
            $comment->update($request->only('body'));

            return Transformer::ok('Success to update comment data.', new CommentResource($comment));
        } catch (AuthorizationException $th) {
            return Transformer::authorizationFailed();
        } catch (ModelNotFoundException $th) {
            return Transformer::modelNotFound('Comment');
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to update comment data.');
        }
    }

    /**
     * Delete comment data.
     *
     * @param   int|string      $id
     *
     * @return  JsonResponse
     */
    public function destroy($id)
    {
        try {
            $comment = Comment::select('id', 'user_id')->whereId($id)->firstOrFail();

            $this->authorize('delete', $comment);

            $comment->delete();

            return Transformer::ok('Success to delete comment data.');
        } catch (AuthorizationException $th) {
            return Transformer::authorizationFailed();
        } catch (ModelNotFoundException $th) {
            return Transformer::modelNotFound('Comment');
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to delete comment data.');
        }
    }
}
