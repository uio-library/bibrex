<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Scriptotek\Alma\Client;

class WebhooksController extends Controller
{
    /**
     * Alma webhooks router.
     *
     * @param Client $almaClient
     * @param Request $request
     * @return Response
     */
    public function handle(Client $almaClient, Request $request)
    {
        $secret = config('services.alma.webhook_secret');
        $eventType = $request->input('action');
        $hash = $request->header('X-Exl-Signature');

        $expectedHash = base64_encode(
            hash_hmac('sha256', $request->getContent(), $secret, true)
        );

        if (!hash_equals($hash, $expectedHash)) {
            \Log::warning(
                "Ignoring Alma '{$eventType}' event due to invalid signature. " .
                "Expected '{$expectedHash}', got '{$hash}'."
            );

            return response()->json(['errorMessage' => 'Invalid Signature'], 401);
        }

        switch ($eventType) {
            case 'USER':
                return $this->handleUserUpdate($almaClient, $request);

            default:
                return response('No handler for this webhook event type.', 202);
        }
    }

    /**
     * Handler for the Alma user webhook.
     *
     * @param Client $almaClient
     * @param Request $request
     * @return Response
     */
    protected function handleUserUpdate(Client $almaClient, Request $request)
    {
        $data = $request->input('webhook_user');

        $primaryId = array_get($data, 'user.primary_id');

        $almaClientUser = \Scriptotek\Alma\Users\User::make($almaClient, $primaryId)
            ->init(json_decode(json_encode($data['user'])));

        $almaUser = new \App\Alma\User($almaClientUser);

        $localUser = User::where('alma_primary_id', '=', $almaUser->primaryId)
            ->orWhere('university_id', '=', $almaUser->getUniversityId())
            ->first();

        if (is_null($localUser)) {
            \Log::debug('Ignorerer Alma-brukeroppdateringsvarsel for bruker som ikke er i Bibrex.');
        } else {
            $localUser->mergeFromAlmaResponse($almaUser);
            if ($localUser->isDirty()) {
                $localUser->save();
                \Log::info(sprintf(
                    'Oppdaterte brukeren <a href="%s">%s</a> fra Alma (%s %s).',
                    action('UsersController@getShow', $localUser->id),
                    $localUser->name,
                    $data['cause'],
                    $data['method']
                ));
            } else {
                \Log::debug(
                    'Alma-brukeroppdateringsvarsel førte ikke til noen endringer på den tilknyttede Bibrex-brukeren.'
                );
            }
        }

        // Say yo to Alma
        return response('Yo!', 200);
    }
}