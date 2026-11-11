<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config.php';

use App\Repository\MemberRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Google sign-in handler. Uses Google's tokeninfo endpoint to
 * validate the ID token directly — no Google_Client dep needed
 * (older google/apiclient shipped `Google_*` aliases; recent
 * versions dropped them).
 */

const GOOGLE_CLIENT_ID     = '';
const GOOGLE_CLIENT_SECRET = '';
const GOOGLE_TOKENINFO_URL = 'https://www.googleapis.com/oauth2/v3/tokeninfo';

/**
 * @throws Exception
 * @return array<string,mixed>
 */
function manuallyValidateGoogleIdToken(string $idToken, string $clientId): array
{
    $idToken = trim($idToken);
    if ($idToken === '') {
        throw new Exception('Empty idToken');
    }
    try {
        $res = (new Client())->request('GET', GOOGLE_TOKENINFO_URL, [
            'query'       => ['id_token' => $idToken],
            'timeout'     => 10,
            'http_errors' => true,
        ]);
    } catch (GuzzleException $e) {
        if (method_exists($e, 'hasResponse') && $e->hasResponse()) {
            $resp = $e->getResponse();
            $body = json_decode((string) $resp->getBody(), true);
            $msg  = is_array($body) ? ($body['error_description'] ?? $e->getMessage()) : $e->getMessage();
            throw new Exception($msg, $resp->getStatusCode());
        }
        throw new Exception($e->getMessage(), 0, $e);
    }
    $payload = json_decode((string) $res->getBody(), true);
    if (!is_array($payload)) {
        throw new Exception('Invalid tokeninfo response');
    }
    if (($payload['aud'] ?? null) !== $clientId) {
        throw new Exception("App Isn't valid", 422);
    }
    return $payload;
}

try {
    $data = json_decode(file_get_contents('php://input') ?: 'null');
    if (!isset($data->idToken)) {
        throw new Exception('"idToken" parameter not provided');
    }

    $payload = (object) manuallyValidateGoogleIdToken((string) $data->idToken, GOOGLE_CLIENT_ID);

    $members = new MemberRepository();
    $user = $members->findByEmail((string) $payload->email);

    if ($user !== null) {
        $members->updateById((int) $user->id, ['google_uid' => (string) $payload->sub]);
    } else {
        $imgFilename = 'google_img_' . $payload->sub . '.jpg';
        (new Client())->request(
            'GET',
            $payload->picture,
            [
                'sink'    => fopen(__DIR__ . '/../../files/user_pic/' . $imgFilename, 'w'),
                'timeout' => 15,
            ],
        );

        $newId = $members->create([
            'google_uid' => (string) $payload->sub,
            'email'      => (string) $payload->email,
            'username'   => (string) $payload->name,
            'pass'       => '--dummy-password--' . bin2hex(random_bytes(22)),
            'name'       => (string) $payload->name,
            'pic'        => $imgFilename,
            'stat'       => 1,
        ]);
        $user = $members->find((int) $newId);
    }

    $_SESSION['user'] = $user->id . '_' . $user->pass;

    echo json_encode(['isSuccessful' => true, 'memberId' => $user->id]);
} catch (Throwable $e) {
    header(($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1') . ' 500 Internal Server Error', true, 500);
    echo json_encode(['isSuccessful' => false, 'error' => $e->getMessage()]);
}
