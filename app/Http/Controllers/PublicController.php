<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\GalleryImage;
use App\Models\GymClass;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\PersonalTrainingPackage;
use App\Models\User;
use App\Notifications\NewContactMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class PublicController extends Controller
{
    public function home()
    {
        // Exclude Lifetime from the homepage teaser (its NULL duration would otherwise
        // sort first in MySQL, showing the 80,000 BDT plan before any starter option)
        // and feature only fixed-term plans here — the full comparison lives on
        // the dedicated Membership Plans page.
        $plans = MembershipPlan::where('is_active', true)
            ->where('is_lifetime', false)
            ->orderBy('duration_in_months')
            ->limit(3)
            ->get();
        $images = GalleryImage::where('is_active', true)->orderBy('sort_order')->limit(6)->get();
        $trainers = User::role('Trainer')->with('trainerProfile')->limit(3)->get();
        $stats = [
            'members' => Member::count(),
            'trainers' => User::role('Trainer')->count(),
            'classes' => GymClass::where('is_active', true)->count(),
        ];

        return view('public.home', compact('plans', 'images', 'trainers', 'stats'));
    }

    public function about()
    {
        $stats = [
            'members' => Member::count(),
            'trainers' => User::role('Trainer')->count(),
        ];

        return view('public.about', compact('stats'));
    }

    public function membershipPlans()
    {
        // Lifetime's duration_in_months is NULL, which MySQL sorts before any
        // number — order NULLs last so Lifetime displays after the fixed-term
        // plans instead of before Monthly.
        $plans = MembershipPlan::where('is_active', true)
            ->orderByRaw('duration_in_months IS NULL, duration_in_months ASC')
            ->get();

        return view('public.membership-plans', compact('plans'));
    }

    public function personalTraining()
    {
        $packages = PersonalTrainingPackage::where('is_active', true)->get();
        $trainers = User::role('Trainer')->with('trainerProfile')->get();

        return view('public.personal-training', compact('packages', 'trainers'));
    }

    public function weightTraining()
    {
        return view('public.weight-training');
    }

    public function classes()
    {
        $classes = GymClass::where('is_active', true)
            ->with('trainer')
            ->orderByRaw("FIELD(day_of_week,'monday','tuesday','wednesday','thursday','friday','saturday','sunday')")
            ->orderBy('start_time')
            ->get();

        return view('public.classes', compact('classes'));
    }

    public function dietNutrition()
    {
        return view('public.diet-nutrition');
    }

    public function tipsTricks()
    {
        return view('public.tips-tricks');
    }

    public function gallery()
    {
        $images = GalleryImage::where('is_active', true)->orderBy('sort_order')->get();

        return view('public.gallery', compact('images'));
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function contactStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $message = ContactMessage::create($data);

        $admins = User::role('Super Admin')->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewContactMessageNotification($message));
        }

        return back()->with('status', "Thanks {$data['name']}, we've received your message and will get back to you soon.");
    }
}
