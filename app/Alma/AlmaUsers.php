<?php declare(strict_types=1);

namespace App\Alma;

use App\User as LocalUser;
use App\UserIdentifier;
use Scriptotek\Alma\Client as AlmaClient;

class AlmaUsers
{
    /**
     * @var AlmaClient
     */
    protected $alma;

    /**
     * AlmaUsers constructor.
     * @param AlmaClient $alma
     */
    public function __construct(AlmaClient $alma)
    {
        $this->alma = $alma;
    }

    public function hasKey()
    {
        return !is_null($this->alma->key);
    }

    /**
     * Find Alma user by some ID.
     *
     * @param string $userId
     * @return User|null
     */
    public function findById(?string $userId)
    {
        return User::lookup($this->alma, $userId);
    }

    /**
     * Find Alma user from local user.
     *
     * @param LocalUser $user
     * @return User|null
     */
    public function findFromLocalUser(LocalUser $user)
    {
        if (!is_null($user->alma_primary_id)) {
            $almaUser = $this->findById($user->alma_primary_id);
            if (!is_null($almaUser)) {
                return $almaUser;
            }
        }
        foreach ($user->identifiers as $identifier) {
            $almaUser = $this->findById($identifier['value']);
            if (!is_null($almaUser)) {
                return $almaUser;
            }
        }
        return null;
    }

    /**
     * Find local User from Alma User.
     *
     * @param User $almaUser
     * @return LocalUser|null
     */
    public function findLocalUserFromAlmaUser(User $almaUser)
    {
        if (!is_null($almaUser->primaryId)) {
            $localUser = LocalUser::where('alma_primary_id', '=', $almaUser->primaryId)->first();
            if (!is_null($localUser)) {
                return $localUser;
            }
        }
        foreach ($almaUser->getIdentifiers() as $identifier) {
            if ($identifier->status == 'ACTIVE') {
                $userIdent = UserIdentifier::where('value', '=', $identifier->value)->first();
                if (!is_null($userIdent)) {
                    return $userIdent->user;
                }
            }
        }
        return null;
    }

    /**
     * Update the local user object with user data from Alma.
     * Returns true if successful, false if the user could no longer be found in Alma.
     *
     * @param LocalUser $localUser
     * @param User|null $almaUser
     * @return bool
     */
    public function updateLocalUserFromAlmaUser(LocalUser $localUser, User $almaUser = null)
    {
        if (is_null($almaUser)) {
            $almaUser = $this->findFromLocalUser($localUser);
        }

        if (is_null($almaUser)) {
            $localUser->in_alma = false;
            return false;
        }

        // Set user props
        $localUser->in_alma = true;
        $localUser->setAlmaPrimaryId($almaUser->primaryId, true);
        $localUser->alma_user_group = $almaUser->group;
        $localUser->lastname = $almaUser->lastName;
        $localUser->firstname = $almaUser->firstName;
        $localUser->email = $almaUser->email;
        $localUser->phone = $almaUser->phone;
        $localUser->lang = $almaUser->lang;
        $localUser->blocks = $almaUser->blocks;
        $localUser->fees = $almaUser->getFees();
        $localUser->save();

        // Set user identifiers
        $identifiers = [];
        foreach ($almaUser->getBarcodes() as $value) {
            $identifiers[] = ['type' => 'barcode', 'value' => $value];
        }
        foreach ($almaUser->getUniversityIds() as $value) {
            $identifiers[] = ['type' => 'university_id', 'value' => $value];
        }
        $localUser->setIdentifiers($identifiers, true);

        return true;
    }

    /**
     * Update or create local user from Alma user.
     *
     * @param User $almaUser
     * @return LocalUser|null
     */
    public function updateOrCreateLocalUserFromAlmaUser(User $almaUser)
    {
        $localUser = $this->findLocalUserFromAlmaUser($almaUser) ?? new LocalUser();

        $this->updateLocalUserFromAlmaUser($localUser, $almaUser);

        \Log::info(sprintf(
            'Importerte en bruker fra Alma (<a href="%s">Detaljer</a>)',
            action('UsersController@getShow', $localUser->id)
        ));

        return $localUser;
    }
}
