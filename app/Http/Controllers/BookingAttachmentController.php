<?php

namespace App\Http\Controllers;

use App\Models\BookingAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingAttachmentController extends Controller
{
    public function download(Request $request, BookingAttachment $bookingAttachment): StreamedResponse
    {
        $profile = $request->user()->providerProfile;

        abort_unless(
            $profile && $bookingAttachment->booking()->where('provider_profile_id', $profile->id)->exists(),
            404
        );

        return Storage::disk('public')->download(
            $bookingAttachment->path,
            $bookingAttachment->original_name
        );
    }
}
