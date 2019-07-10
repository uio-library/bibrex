<?php declare(strict_types=1);

namespace App\Alma;

use App\User as LocalUser;
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
        return $this->findById($user->university_id) ?? $this->findById($user->barcode);
    }

    /**
     * Find local User from Alma User.
     *
     * @param User $almaUser
     * @return LocalUser|null
     */
    public function findLocalUserFromAlmaUser(User $almaUser)
    {
        $builder = LocalUser::query();
        if (!is_null($almaUser->primaryId)) {
            $builder->orWhere('alma_primary_id', '=', $almaUser->primaryId);
        }
        if (!is_null($almaUser->getBarcode())) {
            $builder->orWhere('barcode', '=', $almaUser->getBarcode());
        }
        if (!is_null($almaUser->getUniversityId())) {
            $builder->orWhere('university_id', '=', $almaUser->getUniversityId());
        }

        if (count($builder->getQuery()->wheres)) {
            return $builder->first();
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

        $localUser->in_alma = true;

        $localUser->setUniqueValue('alma_primary_id', $almaUser->primaryId);
        $localUser->setUniqueValue('barcode', $almaUser->getBarcode());
        $localUser->setUniqueValue('university_id', $almaUser->getUniversityId());

        $localUser->alma_user_group = $almaUser->group;
        $localUser->lastname = $almaUser->lastName;
        $localUser->firstname = $almaUser->firstName;
        $localUser->email = $almaUser->email;
        $localUser->phone = $almaUser->phone;
        $localUser->lang = $almaUser->lang;
        $localUser->blocks = $almaUser->blocks;
        $localUser->fees = $almaUser->getFees();

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

        $localUser->save();

        \Log::info(sprintf(
            'Importerte en bruker fra Alma (<a href="%s">Detaljer</a>)',
            action('UsersController@getShow', $localUser->id)
        ));

        return $localUser;
    }
}
