<?php

namespace App\Http\Controllers;

// ponytail: unified index and action methods for both Siswa and Guru to avoid route and controller duplication.

use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ForumController extends Controller
{
    private function checkAccess($classId, $subjectId)
    {
        $user = Auth::user();
        if ($user->role === 'siswa') {
            $student = Student::where('user_id', $user->id)->first();
            if (!$student || $student->class_id != $classId) {
                abort(403, 'Akses ditolak. Anda bukan siswa di kelas ini.');
            }
        } elseif ($user->role === 'guru') {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher) {
                abort(403, 'Akses ditolak. Anda bukan guru.');
            }
        }
    }

    public function index($classSlug, $subjectSlug): View
    {
        $schoolClass = SchoolClass::where('slug', $classSlug)->firstOrFail();
        $subject = Subject::where('slug', $subjectSlug)->firstOrFail();

        $this->checkAccess($schoolClass->id, $subject->id);

        $posts = ForumPost::where('class_id', $schoolClass->id)
            ->where('subject_id', $subject->id)
            ->whereNull('meeting_id')
            ->with([
                'user',
                'comments' => function($q) { $q->whereNull('parent_id')->with('user'); },
                'comments.replies.user',
                'comments.replies.replies.user',
                'comments.replies.replies.replies.user'
            ])
            ->latest()
            ->paginate(10);

        return view('forum.index', compact('schoolClass', 'subject', 'posts'));
    }

    public function storePost(Request $request, $classSlug, $subjectSlug): RedirectResponse
    {
        $schoolClass = SchoolClass::where('slug', $classSlug)->firstOrFail();
        $subject = Subject::where('slug', $subjectSlug)->firstOrFail();

        $this->checkAccess($schoolClass->id, $subject->id);

        $data = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $post = ForumPost::create([
            'class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'user_id' => Auth::id(),
            'content' => $data['content'],
        ]);

        try {
            $user = Auth::user();
            $snippet = mb_strimwidth(strip_tags($post->content), 0, 80, '...');
            
            if ($user->role === 'siswa') {
                $cst = \App\Models\ClassSubjectTeacher::where('class_id', $schoolClass->id)
                    ->where('subject_id', $subject->id)
                    ->first();
                if ($cst && $cst->teacher && $cst->teacher->user_id !== $user->id) {
                    \App\Models\Notification::create([
                        'user_id' => $cst->teacher->user_id,
                        'title' => '💬 Forum Baru: ' . $user->name,
                        'message' => $snippet,
                        'url' => route('forum.index', ['classSlug' => $classSlug, 'subjectSlug' => $subjectSlug]),
                    ]);
                }
            } elseif ($user->role === 'guru') {
                $students = Student::where('class_id', $schoolClass->id)->get();
                foreach ($students as $student) {
                    if ($student->user_id !== $user->id) {
                        \App\Models\Notification::create([
                            'user_id' => $student->user_id,
                            'title' => '💬 Pengumuman Forum: ' . $user->name,
                            'message' => $snippet,
                            'url' => route('forum.index', ['classSlug' => $classSlug, 'subjectSlug' => $subjectSlug]),
                        ]);
                    }
                }
            }
        } catch (\Exception $ne) {
            // Ignore
        }

        return back()->with('success', 'Postingan berhasil dibagikan.');
    }

    public function storeComment(Request $request, ForumPost $post): RedirectResponse
    {
        $this->checkAccess($post->class_id, $post->subject_id);

        $data = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'exists:forum_comments,id'],
        ]);

        $comment = ForumComment::create([
            'forum_post_id' => $post->id,
            'user_id' => Auth::id(),
            'content' => $data['content'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        try {
            $user = Auth::user();
            $notificationTargetUserId = null;
            $notificationTitle = '';
            
            if ($comment->parent_id) {
                $parentComment = ForumComment::find($comment->parent_id);
                if ($parentComment && $parentComment->user_id !== $user->id) {
                    $notificationTargetUserId = $parentComment->user_id;
                    $notificationTitle = '💬 Balasan Baru di Komentar Anda';
                }
            } elseif ($post->user_id !== $user->id) {
                $notificationTargetUserId = $post->user_id;
                $notificationTitle = '💬 Komentar Baru di Postingan Anda';
            }

            if ($notificationTargetUserId) {
                \App\Models\Notification::create([
                    'user_id' => $notificationTargetUserId,
                    'title' => $notificationTitle,
                    'message' => $user->name . ': ' . mb_strimwidth(strip_tags($comment->content), 0, 80, '...'),
                    'url' => route('forum.index', ['classSlug' => $post->schoolClass->slug, 'subjectSlug' => $post->subject->slug]),
                ]);
            }
        } catch (\Exception $ne) {
            // Ignore
        }

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }

    public function destroyPost(ForumPost $post): RedirectResponse
    {
        $user = Auth::user();
        if ($post->user_id !== $user->id && $user->role !== 'guru' && $user->role !== 'admin') {
            abort(403, 'Anda tidak memiliki hak untuk menghapus postingan ini.');
        }

        $post->delete();

        return back()->with('success', 'Postingan berhasil dihapus.');
    }

    // === MEETING DISCUSSION ===

    private function checkMeetingAccess($meeting)
    {
        $user = Auth::user();
        if ($user->role === 'siswa') {
            $student = Student::where('user_id', $user->id)->first();
            if (!$student || $student->class_id != $meeting->class_id) {
                abort(403, 'Akses ditolak.');
            }
            if (!$meeting->is_visible) {
                abort(403, 'Akses ditolak.');
            }
        } elseif ($user->role === 'guru') {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher) {
                abort(403, 'Akses ditolak.');
            }
        }
    }

    public function storeMeetingPost(Request $request, \App\Models\Meeting $meeting): RedirectResponse
    {
        $this->checkMeetingAccess($meeting);

        $data = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $post = ForumPost::create([
            'class_id' => $meeting->class_id,
            'subject_id' => $meeting->subject_id,
            'meeting_id' => $meeting->id,
            'user_id' => Auth::id(),
            'content' => $data['content'],
        ]);

        try {
            $user = Auth::user();
            $snippet = mb_strimwidth(strip_tags($post->content), 0, 80, '...');

            if ($user->role === 'siswa') {
                $teacherUser = $meeting->teacher?->user;
                if ($teacherUser && $teacherUser->id !== $user->id) {
                    \App\Models\Notification::create([
                        'user_id' => $teacherUser->id,
                        'title' => '💬 Diskusi Baru: ' . $user->name,
                        'message' => $snippet,
                        'url' => route('guru.meetings.show', $meeting->id),
                    ]);
                }
            } else {
                $students = Student::where('class_id', $meeting->class_id)->get();
                foreach ($students as $student) {
                    if ($student->user_id !== $user->id) {
                        \App\Models\Notification::create([
                            'user_id' => $student->user_id,
                            'title' => '💬 Diskusi Kelas Baru: ' . $user->name,
                            'message' => $snippet,
                            'url' => route('siswa.meetings.show', $meeting->id),
                        ]);
                    }
                }
            }
        } catch (\Exception $ne) {
            // Ignore
        }

        return back()->with('success', 'Diskusi berhasil ditambahkan.');
    }

    public function storeMeetingComment(Request $request, ForumPost $post): RedirectResponse
    {
        $meeting = \App\Models\Meeting::findOrFail($post->meeting_id);
        $this->checkMeetingAccess($meeting);

        $data = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'exists:forum_comments,id'],
        ]);

        $comment = ForumComment::create([
            'forum_post_id' => $post->id,
            'user_id' => Auth::id(),
            'content' => $data['content'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        try {
            $user = Auth::user();
            $notificationTargetUserId = null;
            $notificationTitle = '';
            
            if ($comment->parent_id) {
                $parentComment = ForumComment::find($comment->parent_id);
                if ($parentComment && $parentComment->user_id !== $user->id) {
                    $notificationTargetUserId = $parentComment->user_id;
                    $notificationTitle = '💬 Balasan Diskusi Baru di Komentar Anda';
                }
            } elseif ($post->user_id !== $user->id) {
                $notificationTargetUserId = $post->user_id;
                $notificationTitle = '💬 Balasan Diskusi Baru';
            }

            if ($notificationTargetUserId) {
                $targetUrl = $post->user?->role === 'guru' 
                    ? route('guru.meetings.show', $meeting->id) 
                    : route('siswa.meetings.show', $meeting->id);

                \App\Models\Notification::create([
                    'user_id' => $notificationTargetUserId,
                    'title' => $notificationTitle,
                    'message' => $user->name . ': ' . mb_strimwidth(strip_tags($comment->content), 0, 80, '...'),
                    'url' => $targetUrl,
                ]);
            }
        } catch (\Exception $ne) {
            // Ignore
        }

        return back()->with('success', 'Balasan berhasil ditambahkan.');
    }

    public function destroyMeetingPost(ForumPost $post): RedirectResponse
    {
        $user = Auth::user();
        if ($post->user_id !== $user->id && $user->role !== 'guru' && $user->role !== 'admin') {
            abort(403, 'Anda tidak memiliki hak untuk menghapus postingan ini.');
        }

        $post->delete();

        return back()->with('success', 'Diskusi berhasil dihapus.');
    }

    public function destroyComment(ForumComment $comment): RedirectResponse
    {
        $user = Auth::user();
        if ($comment->user_id !== $user->id && $user->role !== 'guru' && $user->role !== 'admin') {
            abort(403, 'Anda tidak memiliki hak untuk menghapus komentar ini.');
        }

        $comment->delete();

        return back()->with('success', 'Komentar berhasil dihapus.');
    }
}
