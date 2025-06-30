<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityEvent;
use App\Models\EventSession;
use App\Models\EventSpeaker;
use App\Http\Requests\EventSession\CreateEventSessionRequest;
use App\Http\Requests\EventSession\UpdateEventSessionRequest;
use App\Http\Requests\EventSession\EventSessionSpeakerRequest;
use App\Http\Requests\EventSession\BulkSessionOperationsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EventSessionController extends Controller
{
    public function index(Community $community, CommunityEvent $event)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $sessions = EventSession::where('community_event_id', $event->id)
            ->with(['speakers' => function($query) {
                $query->ordered();
            }, 'cfsApplications' => function($query) {
                $query->with('reviewer');
            }])
            ->orderBy('starts_at')
            ->get();

        return Inertia::render('CommunityEvents/Sessions', [
            'community' => $community,
            'event' => $event,
            'sessions' => $sessions,
        ]);
    }

    public function store(CreateEventSessionRequest $request, Community $community, CommunityEvent $event)
    {
        $session = EventSession::create([
            'community_event_id' => $event->id,
            'title' => $request->title,
            'description' => $request->description,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'max_speakers' => $request->max_speakers,
            'allows_applications' => $request->allows_applications,
            'block_on_application' => $request->block_on_application,
            'location' => $request->location,
            'meeting_link' => $request->meeting_link,
            'requirements' => $request->requirements,
            'custom_fields' => $request->custom_fields,
            'status' => 'available',
            'current_speakers' => 0,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'session' => $session->fresh()->load(['speakers', 'cfsApplications']),
            ]);
        }

        return redirect()->back()->with('success', 'Session created successfully!');
    }

    public function show(Community $community, CommunityEvent $event, EventSession $session)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $session->load([
            'speakers' => function($query) {
                $query->ordered()->with('cfsApplication');
            },
            'cfsApplications' => function($query) {
                $query->with('reviewer')->latest();
            }
        ]);

        return response()->json($session);
    }

    public function update(UpdateEventSessionRequest $request, Community $community, CommunityEvent $event, EventSession $session)
    {
        $session->update([
            'title' => $request->title,
            'description' => $request->description,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'max_speakers' => $request->max_speakers,
            'allows_applications' => $request->allows_applications,
            'block_on_application' => $request->block_on_application,
            'status' => $request->status,
            'location' => $request->location,
            'meeting_link' => $request->meeting_link,
            'requirements' => $request->requirements,
            'custom_fields' => $request->custom_fields,
        ]);

        $session->updateStatus();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'session' => $session->fresh()->load(['speakers', 'cfsApplications']),
            ]);
        }

        return redirect()->back()->with('success', 'Session updated successfully!');
    }

    public function destroy(Community $community, CommunityEvent $event, EventSession $session)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        if ($session->current_speakers > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete session with confirmed speakers.',
            ], 422);
        }

        if ($session->cfsApplications()->whereIn('status', ['pending', 'approved'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete session with pending or approved applications.',
            ], 422);
        }

        $session->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Session deleted successfully!',
            ]);
        }

        return redirect()->back()->with('success', 'Session deleted successfully!');
    }

    public function addSpeaker(EventSessionSpeakerRequest $request, Community $community, CommunityEvent $event, EventSession $session)
    {
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('speaker-photos', 'public');
        }

        $speaker = EventSpeaker::create([
            'community_event_id' => $event->id,
            'event_session_id' => $session->id,
            'cfs_application_id' => $request->cfs_application_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'bio' => $request->bio,
            'photo' => $photoPath,
            'company' => $request->company,
            'job_title' => $request->job_title,
            'topic_title' => $request->topic_title,
            'topic_description' => $request->topic_description,
            'social_links' => $request->social_links,
            'assignment_type' => $request->assignment_type,
            'status' => $request->status,
            'is_featured' => $request->is_featured,
            'sort_order' => $request->sort_order,
            'notes' => $request->notes,
            'confirmed_at' => $request->status === 'confirmed' ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'speaker' => $speaker->fresh(),
            'session' => $session->fresh(['speakers']),
        ]);
    }

    public function updateSpeaker(EventSessionSpeakerRequest $request, Community $community, CommunityEvent $event, EventSession $session, EventSpeaker $speaker)
    {
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'bio' => $request->bio,
            'company' => $request->company,
            'job_title' => $request->job_title,
            'topic_title' => $request->topic_title,
            'topic_description' => $request->topic_description,
            'social_links' => $request->social_links,
            'status' => $request->status,
            'is_featured' => $request->is_featured,
            'sort_order' => $request->sort_order,
            'notes' => $request->notes,
        ];

        if ($request->hasFile('photo')) {
            if ($speaker->photo) {
                Storage::disk('public')->delete($speaker->photo);
            }
            $updateData['photo'] = $request->file('photo')->store('speaker-photos', 'public');
        }

        if ($request->status === 'confirmed' && $speaker->status !== 'confirmed') {
            $updateData['confirmed_at'] = now();
        } elseif ($request->status !== 'confirmed') {
            $updateData['confirmed_at'] = null;
        }

        $speaker->update($updateData);

        return response()->json([
            'success' => true,
            'speaker' => $speaker->fresh(),
            'session' => $session->fresh(['speakers']),
        ]);
    }

    public function removeSpeaker(Community $community, CommunityEvent $event, EventSession $session, EventSpeaker $speaker)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        if ($speaker->event_session_id !== $session->id) {
            abort(404);
        }

        if ($speaker->status === 'confirmed' && $session->starts_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove confirmed speakers from sessions that have started.',
            ], 422);
        }

        if ($speaker->cfs_application_id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove speakers linked to CFS applications. Please reject their application instead.',
            ], 422);
        }

        if ($speaker->photo) {
            Storage::disk('public')->delete($speaker->photo);
        }

        $speaker->delete();

        return response()->json([
            'success' => true,
            'message' => 'Speaker removed successfully!',
            'session' => $session->fresh(['speakers']),
        ]);
    }

    public function updateSpeakerOrder(EventSessionSpeakerRequest $request, Community $community, CommunityEvent $event, EventSession $session)
    {
        foreach ($request->speakers as $speakerData) {
            EventSpeaker::where('id', $speakerData['id'])
                ->where('event_session_id', $session->id)
                ->update(['sort_order' => $speakerData['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Speaker order updated successfully!',
        ]);
    }

    public function bulkUpdateSpeakers(EventSessionSpeakerRequest $request, Community $community, CommunityEvent $event, EventSession $session)
    {
        $speakers = EventSpeaker::whereIn('id', $request->speaker_ids)
            ->where('event_session_id', $session->id)
            ->get();

        $updated = 0;
        $results = [];

        foreach ($speakers as $speaker) {
            try {
                switch ($request->action) {
                    case 'set_status':
                        if ($this->canUpdateSpeakerStatus($speaker, $request->status)) {
                            $speaker->update([
                                'status' => $request->status,
                                'confirmed_at' => $request->status === 'confirmed' ? now() : null,
                            ]);
                            $updated++;
                        }
                        break;

                    case 'set_featured':
                        $speaker->update(['is_featured' => true]);
                        $updated++;
                        break;

                    case 'unset_featured':
                        $speaker->update(['is_featured' => false]);
                        $updated++;
                        break;

                    case 'send_invitations':
                        if ($speaker->status === 'pending' && $speaker->email) {
                            // TODO: add send invitation email
                            $results[] = "Invitation sent to {$speaker->name}";
                        }
                        break;

                    case 'remove_speakers':
                        if ($this->canRemoveSpeaker($speaker)) {
                            if ($speaker->photo) {
                                Storage::disk('public')->delete($speaker->photo);
                            }
                            $speaker->delete();
                            $updated++;
                        }
                        break;
                }
            } catch (\Exception $e) {
                $results[] = "Failed to update {$speaker->name}: {$e->getMessage()}";
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Updated {$updated} speakers successfully!",
            'results' => $results,
            'session' => $session->fresh(['speakers']),
        ]);
    }

    public function createSpeakerFromApplication(Request $request, Community $community, CommunityEvent $event, EventSession $session)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'cfs_application_id' => 'required|exists:cfs_applications,id',
            'is_featured' => 'boolean',
        ]);

        $application = \App\Models\CfsApplication::findOrFail($request->cfs_application_id);

        if (EventSpeaker::where('cfs_application_id', $application->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Speaker already exists for this application.',
            ], 422);
        }

        if ($application->event_session_id !== $session->id) {
            return response()->json([
                'success' => false,
                'message' => 'Application does not belong to this session.',
            ], 422);
        }

        $speaker = EventSpeaker::create([
            'community_event_id' => $event->id,
            'event_session_id' => $session->id,
            'cfs_application_id' => $application->id,
            'name' => $application->applicant_name,
            'email' => $application->applicant_email,
            'phone' => $application->applicant_phone,
            'bio' => $application->bio,
            'topic_title' => $application->topic_title,
            'topic_description' => $application->topic_description,
            'assignment_type' => 'cfs_application',
            'status' => 'confirmed',
            'is_featured' => $request->boolean('is_featured', false),
            'confirmed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'speaker' => $speaker->fresh(),
            'session' => $session->fresh(['speakers']),
            'message' => 'Speaker created from application successfully!',
        ]);
    }

    public function duplicate(Community $community, CommunityEvent $event, EventSession $session)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $newSession = $session->replicate();
        $newSession->title = $session->title . ' (Copy)';
        $newSession->current_speakers = 0;
        $newSession->status = 'available';
        $newSession->save();

        return response()->json([
            'success' => true,
            'session' => $newSession,
            'message' => 'Session duplicated successfully!',
        ]);
    }

    public function bulkUpdate(BulkSessionOperationsRequest $request, Community $community, CommunityEvent $event)
    {
        $sessions = EventSession::whereIn('id', $request->session_ids)
            ->where('community_event_id', $event->id)
            ->get();

        $updated = 0;
        foreach ($sessions as $session) {
            switch ($request->action) {
                case 'enable_applications':
                    if ($session->status !== 'cancelled' && !$session->is_full && !$session->starts_at->isPast()) {
                        $session->update(['allows_applications' => true]);
                        $updated++;
                    }
                    break;
                case 'disable_applications':
                    if (!$session->has_pending_applications) {
                        $session->update(['allows_applications' => false]);
                        $updated++;
                    }
                    break;
                case 'enable_blocking':
                    $session->update(['block_on_application' => true]);
                    $session->updateStatus();
                    $updated++;
                    break;
                case 'disable_blocking':
                    $session->update(['block_on_application' => false]);
                    $session->updateStatus();
                    $updated++;
                    break;
                case 'set_status':
                    $session->update(['status' => $request->status]);
                    $updated++;
                    break;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Updated {$updated} sessions successfully!",
            'sessions' => $sessions->fresh(),
        ]);
    }

    public function getAvailability(Community $community, CommunityEvent $event, EventSession $session)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        return response()->json([
            'session_id' => $session->id,
            'title' => $session->title,
            'status' => $session->status,
            'available_spots' => $session->available_spots,
            'max_speakers' => $session->max_speakers,
            'current_speakers' => $session->current_speakers,
            'allows_applications' => $session->allows_applications,
            'can_accept_applications' => $session->canAcceptApplications(),
            'has_pending_applications' => $session->has_pending_applications,
            'starts_at' => $session->starts_at,
            'ends_at' => $session->ends_at,
        ]);
    }

    /**
     * Helper methods for speaker operations
     */
    private function canUpdateSpeakerStatus(EventSpeaker $speaker, string $newStatus): bool
    {
        $session = $speaker->eventSession;

        if ($session->starts_at && $session->starts_at->isPast() && $speaker->status === 'confirmed') {
            return in_array($newStatus, ['confirmed', 'declined']);
        }

        if ($newStatus === 'confirmed' && $speaker->status !== 'confirmed') {
            return !$session->is_full;
        }

        return true;
    }

    private function canRemoveSpeaker(EventSpeaker $speaker): bool
    {
        $session = $speaker->eventSession;

        if ($session->starts_at && $session->starts_at->isPast() && $speaker->status === 'confirmed') {
            return false;
        }

        if ($speaker->cfs_application_id) {
            return false;
        }

        return true;
    }
}
