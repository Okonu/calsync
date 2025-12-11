<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CallForSpeakers;
use App\Models\CommunityEvent;
use App\Models\EventSession;
use App\Http\Requests\CallForSpeaker\CreateCfsRequest;
use App\Http\Requests\CallForSpeaker\UpdateCfsRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CallForSpeakersController extends Controller
{
    public function index(Community $community)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $cfs = CallForSpeakers::where('community_id', $community->id)
            ->withCount(['applications', 'events'])
            ->latest()
            ->paginate(10);

        return Inertia::render('CallForSpeakers/Index', [
            'community' => $community,
            'cfs' => $cfs,
        ]);
    }

    public function create(Community $community)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        return Inertia::render('CallForSpeakers/Create', [
            'community' => $community,
            'applicationTypes' => [
                'event' => 'Apply to entire events',
                'session' => 'Apply to specific sessions',
                'both' => 'Allow both options',
            ],
        ]);
    }

    public function store(CreateCfsRequest $request, Community $community)
    {
        $cfs = CallForSpeakers::create([
            'community_id' => $community->id,
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'guidelines' => $request->guidelines,
            'opens_at' => $request->opens_at,
            'closes_at' => $request->closes_at,
            'is_public' => $request->is_public,
            'requires_login' => $request->requires_login,
            'show_application_count' => $request->show_application_count,
            'allow_multiple_applications' => $request->allow_multiple_applications,
            'application_type' => $request->application_type,
            'required_fields' => $request->required_fields,
            'custom_questions' => $request->custom_questions,
            'status' => 'draft',
            'auto_approve' => $request->auto_approve,
            'acceptance_email_template' => $request->acceptance_email_template,
            'rejection_email_template' => $request->rejection_email_template,
        ]);

        return redirect()->route('communities.cfs.show', [$community, $cfs])
            ->with('success', 'Call for Speakers created successfully!');
    }

    public function show(Community $community, CallForSpeakers $cfs)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $cfs->load([
            'applications' => function($query) {
                $query->with(['eventSession', 'communityEvent', 'reviewer'])
                    ->latest();
            },
            'events.sessions'
        ]);

        $stats = [
            'total_applications' => $cfs->total_applications,
            'pending_applications' => $cfs->pending_applications,
            'approved_applications' => $cfs->approved_applications,
            'rejected_applications' => $cfs->applications()->where('status', 'rejected')->count(),
        ];

        $cfsData = $cfs->toArray();
        $cfsData['available_actions'] = $this->getAvailableActions($cfs);

        return Inertia::render('CallForSpeakers/Show', [
            'community' => $community,
            'cfs' => $cfsData,
            'stats' => $stats,
        ]);
    }

    public function edit(Community $community, CallForSpeakers $cfs)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        return Inertia::render('CallForSpeakers/Edit', [
            'community' => $community,
            'cfs' => $cfs,
            'applicationTypes' => [
                'event' => 'Apply to entire events',
                'session' => 'Apply to specific sessions',
                'both' => 'Allow both options',
            ],
        ]);
    }

    public function updateStatus(Request $request, Community $community, CallForSpeakers $cfs)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:draft,open,closed,archived'
        ]);

        $newStatus = $request->status;
        $currentStatus = $cfs->status;

        $allowedTransitions = [
            'draft' => ['open', 'archived'],
            'open' => ['closed', 'archived'],
            'closed' => ['open', 'archived', 'draft'],
            'archived' => ['draft']
        ];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
            return back()->withErrors([
                'status' => "Cannot transition from {$currentStatus} to {$newStatus}"
            ]);
        }

        if ($newStatus === 'open') {
            if (empty($cfs->title) || empty($cfs->description)) {
                return back()->withErrors([
                    'status' => 'CFS must have title and description before opening'
                ]);
            }

            if (!$cfs->opens_at) {
                $cfs->opens_at = now();
            }
        }

        $cfs->update(['status' => $newStatus]);

        $message = $this->getStatusChangeMessage($currentStatus, $newStatus);

        return back()->with('success', $message);
    }

    private function getStatusChangeMessage($from, $to)
    {
        $messages = [
            'draft_to_open' => 'Call for Speakers has been published and is now accepting applications',
            'open_to_closed' => 'Call for Speakers has been closed. No new applications will be accepted',
            'closed_to_open' => 'Call for Speakers has been reopened for applications',
            'closed_to_draft' => 'Call for Speakers has been moved back to draft status',
            'any_to_archived' => 'Call for Speakers has been archived'
        ];

        $key = $to === 'archived' ? 'any_to_archived' : "{$from}_to_{$to}";

        return $messages[$key] ?? "Status updated from {$from} to {$to}";
    }

    private function getAvailableActions($cfs)
    {
        $actions = [];
        $status = $cfs->status;

        switch ($status) {
            case 'draft':
                $actions[] = [
                    'key' => 'publish',
                    'label' => 'Publish',
                    'status' => 'open',
                    'class' => 'bg-green-600 hover:bg-green-700 text-white',
                    'icon' => 'publish',
                    'confirm' => 'Are you sure you want to publish this CFS? It will become public and start accepting applications.'
                ];
                $actions[] = [
                    'key' => 'archive',
                    'label' => 'Archive',
                    'status' => 'archived',
                    'class' => 'bg-gray-600 hover:bg-gray-700 text-white',
                    'icon' => 'archive'
                ];
                break;

            case 'open':
                $actions[] = [
                    'key' => 'close',
                    'label' => 'Close',
                    'status' => 'closed',
                    'class' => 'bg-orange-600 hover:bg-orange-700 text-white',
                    'icon' => 'close',
                    'confirm' => 'Are you sure you want to close this CFS? No new applications will be accepted.'
                ];
                $actions[] = [
                    'key' => 'archive',
                    'label' => 'Archive',
                    'status' => 'archived',
                    'class' => 'bg-gray-600 hover:bg-gray-700 text-white',
                    'icon' => 'archive'
                ];
                break;

            case 'closed':
                $actions[] = [
                    'key' => 'reopen',
                    'label' => 'Reopen',
                    'status' => 'open',
                    'class' => 'bg-blue-600 hover:bg-blue-700 text-white',
                    'icon' => 'reopen',
                    'confirm' => 'Are you sure you want to reopen this CFS for new applications?'
                ];
                $actions[] = [
                    'key' => 'unpublish',
                    'label' => 'Back to Draft',
                    'status' => 'draft',
                    'class' => 'bg-yellow-600 hover:bg-yellow-700 text-white',
                    'icon' => 'draft'
                ];
                $actions[] = [
                    'key' => 'archive',
                    'label' => 'Archive',
                    'status' => 'archived',
                    'class' => 'bg-gray-600 hover:bg-gray-700 text-white',
                    'icon' => 'archive'
                ];
                break;

            case 'archived':
                $actions[] = [
                    'key' => 'restore',
                    'label' => 'Restore to Draft',
                    'status' => 'draft',
                    'class' => 'bg-indigo-600 hover:bg-indigo-700 text-white',
                    'icon' => 'restore'
                ];
                break;
        }

        return $actions;
    }

    public function update(UpdateCfsRequest $request, Community $community, CallForSpeakers $cfs)
    {
        $cfs->update($request->only([
            'title', 'slug', 'description', 'guidelines', 'opens_at', 'closes_at',
            'application_type', 'required_fields', 'custom_questions',
            'status', 'acceptance_email_template', 'rejection_email_template',
            'is_public', 'requires_login', 'show_application_count',
            'allow_multiple_applications', 'auto_approve'
        ]));

        return redirect()->back()->with('success', 'Call for Speakers updated successfully!');
    }

    public function destroy(Community $community, CallForSpeakers $cfs)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $cfs->delete();

        return redirect()->route('communities.cfs.index', $community)
            ->with('success', 'Call for Speakers deleted successfully!');
    }

    public function publicList($slug)
    {
        $community = Community::where('slug', $slug)
            ->where('is_public', true)
            ->where('is_active', true)
            ->firstOrFail();

        $cfs = CallForSpeakers::where('community_id', $community->id)
            ->public()
            ->with(['events'])
            ->latest()
            ->paginate(12);

        return Inertia::render('Public/CfsListing', [
            'community' => $community,
            'cfs' => $cfs,
        ]);
    }

    public function publicShow($communitySlug, $cfsSlug)
    {
        $community = Community::where('slug', $communitySlug)
            ->where('is_public', true)
            ->where('is_active', true)
            ->firstOrFail();

        $cfs = CallForSpeakers::where('community_id', $community->id)
            ->where('slug', $cfsSlug)
            ->where('is_public', true)
            ->with([
                'events.sessions' => function($query) {
                    $query->acceptingApplications()->with(['cfsApplications' => function($q) {
                        $q->where('status', 'pending');
                    }]);
                }
            ])
            ->firstOrFail();

        $availableTargets = [];

        if (in_array($cfs->application_type, ['event', 'both'])) {
            $availableTargets['events'] = $cfs->events()
                ->where('status', 'published')
                ->get(['id', 'title', 'description', 'starts_at', 'ends_at']);
        }

        if (in_array($cfs->application_type, ['session', 'both'])) {
            $availableTargets['sessions'] = EventSession::whereIn('community_event_id',
                $cfs->events()->where('status', 'published')->pluck('id')
            )
                ->acceptingApplications()
                ->with('communityEvent:id,title')
                ->get();
        }

        return Inertia::render('Public/CfsDetails', [
            'community' => $community,
            'cfs' => $cfs,
            'availableTargets' => $availableTargets,
            'canApply' => $cfs->acceptsApplications(),
            'applicationCount' => $cfs->show_application_count ? $cfs->total_applications : null,
        ]);
    }

    public function linkEvent(Request $request, Community $community, CallForSpeakers $cfs)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'event_id' => 'required|exists:community_events,id',
        ]);

        $event = CommunityEvent::where('id', $request->event_id)
            ->where('community_id', $community->id)
            ->firstOrFail();

        $event->update(['call_for_speakers_id' => $cfs->id]);

        return response()->json([
            'success' => true,
            'message' => 'Event linked to CFS successfully!',
        ]);
    }

    public function unlinkEvent(Request $request, Community $community, CallForSpeakers $cfs)
    {
        if (!auth()->user()->canManageCommunity($community)) {
            abort(403);
        }

        $request->validate([
            'event_id' => 'required|exists:community_events,id',
        ]);

        $event = CommunityEvent::where('id', $request->event_id)
            ->where('community_id', $community->id)
            ->where('call_for_speakers_id', $cfs->id)
            ->firstOrFail();

        $event->update(['call_for_speakers_id' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Event unlinked from CFS successfully!',
        ]);
    }
}
