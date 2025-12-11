<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CallForSpeakers;
use App\Models\CfsApplication;
use App\Models\CommunityEvent;
use App\Models\EventSession;
use App\Http\Requests\CallForSpeaker\CreateCfsApplicationRequest;
use App\Http\Requests\CallForSpeaker\UpdateCfsApplicationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CfsApplicationController extends Controller
{
    public function apply(CreateCfsApplicationRequest $request, $communitySlug, $cfsSlug)
    {
        $community = Community::where('slug', $communitySlug)
            ->where('is_public', true)
            ->where('is_active', true)
            ->firstOrFail();

        $cfs = CallForSpeakers::where('community_id', $community->id)
            ->where('slug', $cfsSlug)
            ->where('is_public', true)
            ->firstOrFail();

        $eventId = null;
        $sessionId = null;

        if ($request->target_type === 'event') {
            $event = CommunityEvent::where('id', $request->community_event_id)
                ->where('community_id', $community->id)
                ->firstOrFail();
            $eventId = $event->id;
        } elseif ($request->target_type === 'session') {
            $session = EventSession::where('id', $request->event_session_id)
                ->whereHas('communityEvent', function($query) use ($community) {
                    $query->where('community_id', $community->id);
                })
                ->firstOrFail();

            $eventId = $session->community_event_id;
            $sessionId = $session->id;
        }

        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('cfs-attachments/' . $cfs->id, 'public');
                $attachmentPaths[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        $application = CfsApplication::create([
            'call_for_speakers_id' => $cfs->id,
            'community_event_id' => $eventId,
            'event_session_id' => $sessionId,
            'applicant_name' => $request->applicant_name,
            'applicant_email' => $request->applicant_email,
            'applicant_phone' => $request->applicant_phone,
            'bio' => $request->bio,
            'topic_title' => $request->topic_title,
            'topic_description' => $request->topic_description,
            'topic_outline' => $request->topic_outline,
            'experience_level' => $request->experience_level,
            'previous_speaking_experience' => $request->previous_speaking_experience,
            'preferred_sessions' => $request->preferred_sessions,
            'custom_responses' => $request->custom_responses,
            'attachments' => $attachmentPaths,
            'status' => $cfs->auto_approve ? 'approved' : 'pending',
        ]);

        if ($cfs->auto_approve) {
            $application->approve();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully!',
                'tracking_uid' => $application->uid,
            ]);
        }

        return redirect()->route('cfs.application.track', $application->uid)
            ->with('success', 'Application submitted successfully! You can track your application status using the provided link.');
    }

    public function approve(UpdateCfsApplicationRequest $request, CallForSpeakers $cfs, CfsApplication $application)
    {
        $application->approve(auth()->id(), $request->notes);

        if ($request->auto_assign_speaker && $application->eventSession) {
            $speakerData = [
                'community_event_id' => $application->community_event_id,
                'event_session_id' => $application->event_session_id,
                'name' => $application->applicant_name,
                'email' => $application->applicant_email,
                'phone' => $application->applicant_phone,
                'bio' => $application->bio,
                'topic_title' => $application->topic_title,
                'topic_description' => $application->topic_description,
                'assignment_type' => 'cfs_application',
                'cfs_application_id' => $application->id,
                'status' => 'confirmed',
                'is_featured' => $request->input('speaker_assignment.is_featured', false),
                'speaking_order' => $request->input('speaker_assignment.speaking_order'),
                'confirmed_at' => now(),
            ];

            \App\Models\EventSpeaker::create($speakerData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Application approved successfully!',
            'application' => $application->fresh()->load(['eventSession', 'communityEvent']),
        ]);
    }

    public function reject(UpdateCfsApplicationRequest $request, CallForSpeakers $cfs, CfsApplication $application)
    {
        $application->reject($request->reason, auth()->id());

        if ($request->send_notification && $request->custom_email_message) {
            // Here you would send the custom email
            // This could be handled by a job or service
        }

        return response()->json([
            'success' => true,
            'message' => 'Application rejected.',
            'application' => $application->fresh(),
        ]);
    }

    public function bulkApprove(UpdateCfsApplicationRequest $request, CallForSpeakers $cfs)
    {
        $applications = CfsApplication::where('call_for_speakers_id', $cfs->id)
            ->whereIn('id', $request->application_ids)
            ->where('status', 'pending')
            ->get();

        $approved = 0;
        foreach ($applications as $application) {
            if ($application->eventSession && !$application->eventSession->canAcceptApplications()) {
                continue;
            }

            $application->approve(auth()->id(), $request->notes);

            // Handle automatic speaker assignment if requested
            if ($request->auto_assign_speakers && $application->eventSession) {
                $speakerData = [
                    'community_event_id' => $application->community_event_id,
                    'event_session_id' => $application->event_session_id,
                    'name' => $application->applicant_name,
                    'email' => $application->applicant_email,
                    'phone' => $application->applicant_phone,
                    'bio' => $application->bio,
                    'topic_title' => $application->topic_title,
                    'topic_description' => $application->topic_description,
                    'assignment_type' => 'cfs_application',
                    'cfs_application_id' => $application->id,
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ];

                \App\Models\EventSpeaker::create($speakerData);
            }

            $approved++;
        }

        return response()->json([
            'success' => true,
            'message' => "Approved {$approved} applications successfully!",
        ]);
    }

    public function bulkReject(UpdateCfsApplicationRequest $request, CallForSpeakers $cfs)
    {
        $applications = CfsApplication::where('call_for_speakers_id', $cfs->id)
            ->whereIn('id', $request->application_ids)
            ->where('status', 'pending')
            ->get();

        foreach ($applications as $application) {
            $application->reject($request->reason, auth()->id());
        }

        if ($request->send_notifications) {
            // Here you would send bulk rejection emails
            // This could be handled by a job or service
        }

        return response()->json([
            'success' => true,
            'message' => "Rejected {$applications->count()} applications.",
        ]);
    }

    public function show(Request $request, CallForSpeakers $cfs, CfsApplication $application)
    {
        $community = $cfs->community;

        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $application->load(['eventSession', 'communityEvent', 'reviewer']);

        return response()->json($application);
    }

    public function updateNotes(UpdateCfsApplicationRequest $request, CallForSpeakers $cfs, CfsApplication $application)
    {
        $application->update([
            'admin_notes' => $request->notes,
            'internal_notes' => $request->internal_notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notes updated successfully!',
        ]);
    }

    public function updateStatus(UpdateCfsApplicationRequest $request, CallForSpeakers $cfs, CfsApplication $application)
    {
        $oldStatus = $application->status;
        $newStatus = $request->status;

        if ($newStatus === 'approved') {
            $application->approve(auth()->id(), $request->notes);
        } elseif ($newStatus === 'rejected') {
            $application->reject($request->reason, auth()->id());
        } else {
            $application->update([
                'status' => $newStatus,
                'admin_notes' => $request->notes,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Application status updated from {$oldStatus} to {$newStatus}.",
            'application' => $application->fresh(),
        ]);
    }

    public function updateSessionAssignment(UpdateCfsApplicationRequest $request, CallForSpeakers $cfs, CfsApplication $application)
    {
        $application->update([
            'event_session_id' => $request->event_session_id,
            'admin_notes' => $request->notes,
        ]);

        if ($application->eventSpeaker) {
            $application->eventSpeaker->update([
                'event_session_id' => $request->event_session_id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Session assignment updated successfully!',
            'application' => $application->fresh()->load(['eventSession', 'communityEvent']),
        ]);
    }

    public function track($uid)
    {
        $application = CfsApplication::where('uid', $uid)
            ->with(['callForSpeakers.community', 'eventSession', 'communityEvent'])
            ->firstOrFail();

        return Inertia::render('Public/ApplicationTracker', [
            'application' => [
                'uid' => $application->uid,
                'status' => $application->status,
                'applicant_name' => $application->applicant_name,
                'topic_title' => $application->topic_title,
                'created_at' => $application->created_at,
                'reviewed_at' => $application->reviewed_at,
                'rejection_reason' => $application->rejection_reason,
                'cfs' => [
                    'title' => $application->callForSpeakers->title,
                    'community' => [
                        'name' => $application->callForSpeakers->community->name,
                        'slug' => $application->callForSpeakers->community->slug,
                    ],
                ],
                'target' => $application->eventSession
                    ? ['type' => 'session', 'title' => $application->eventSession->title]
                    : ['type' => 'event', 'title' => $application->communityEvent->title],
            ],
        ]);
    }

    public function withdraw($uid)
    {
        $application = CfsApplication::where('uid', $uid)
            ->where('status', 'pending')
            ->firstOrFail();

        $application->update(['status' => 'withdrawn']);

        return redirect()->back()
            ->with('success', 'Application withdrawn successfully.');
    }

    public function downloadAttachment(CfsApplication $application, $index)
    {
        $attachments = $application->attachments ?? [];

        if (!isset($attachments[$index]) || !isset($attachments[$index]['path'])) {
            abort(404);
        }

        $attachment = $attachments[$index];
        $filePath = storage_path('app/public/' . $attachment['path']);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download(
            $filePath,
            $attachment['original_name'] ?? 'attachment',
            ['Content-Type' => $attachment['mime_type'] ?? 'application/octet-stream']
        );
    }
}
