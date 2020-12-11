<?php

namespace App\Http\Controllers;

use App\Helpers\Transformer;
use App\Http\Resources\CommentResource;
use App\Jobs\SetLastCommentJob;
use App\Jobs\SetNotificationJob;
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
            $post = Post::select('id', 'title', 'user_id')->whereId($request->get('_post_id'))->firstOrFail();

            // Create comment
            $comment = $post->comments()->create([
                'user_id' => Auth::id(),
                'body' => $request->get('body'),
            ]);

            // Set notifications.
            dispatch(new SetNotificationJob(
                $post->author,
                Auth::user(),
                $post,
                'post',
                'comment'
            ));

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
    public function markSolution($id)
    {
        try {
            $comment = Comment::select('id', 'post_id')->whereId($id)->firstOrFail();
            $post = $comment->post()->select('id', 'user_id')->first();
            $post_author = $post->author()->select('id')->first();
            $auth = Auth::user();

            if ($auth->id !== $post_author->id) {
                return Transformer::authorizationFailed();
            }

            // Unmark the previous solution
            $post->comments()->where('solution', 'Y')->update([
                'solution' => 'N'
            ]);

            $comment->update([
                'solution' => 'Y'
            ]);

            return Transformer::ok('Success to update comment data.');
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
            $comment = Comment::select('id', 'post_id', 'user_id')->whereId($id)->firstOrFail();

            $this->authorize('delete', $comment);

            $comment->delete();

            $post = $comment->post()->select('id', 'user_id')->first();

            // Set Last Comment
            dispatch(new SetLastCommentJob(Auth::user(), $post));

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
