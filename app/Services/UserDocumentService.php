<?php
/**
 * Created by PhpStorm.
 * User: rohan
 * Date: 11/27/17
 * Time: 5:08 PM
 */

namespace App\Services;

use App\Services\InfusionSoftService;
use App\Services\UserService;

use App\FuseDocument;

class UserDocumentService
{
    protected $repo;

    protected $infusionSoftService;

    protected $userService;


    public function __construct(InfusionSoftService $infusionSoftService, UserService $userService)
    {
        $this->infusionSoftService = $infusionSoftService;
        $this->userService = $userService;
    }

    public function updatePandaId($id, $pandaId, $roles)
    {
        return FuseDocument::where('id', $id)->update([ 'pandaDocId' => $pandaId, 'role_data' => json_encode($roles) ]);
    }

    public function getRecordOnPandaId($id)
    {
        return FuseDocument::where('pandaDocId', $id)->first();
    }

    public function getUserDocumentCount($id)
    {
        return FuseDocument::where('user_id', $id)->get()->count();
    }

    public function applyTagOnDocCount($userId)
    {
        $count = $this->getUserDocumentCount($userId);
        $contactId = $this->userService->getInfusionContactId($userId);
        $result = "";
        switch ($count) {
            case 1:
                $result = $this->infusionSoftService->applyTagToContactInAdminInfusionSoftAccount($contactId, 'Sent First Proposal');
                break;
            case 10:
                $result = $this->infusionSoftService->applyTagToContactInAdminInfusionSoftAccount($contactId, 'Sent 10 Proposals');
                break;
            case 450:
                $result = $this->infusionSoftService->applyTagToContactInAdminInfusionSoftAccount($contactId, 'Sent 450 Docs In One Month');
                break;
            case 1300:
                $result = $this->infusionSoftService->applyTagToContactInAdminInfusionSoftAccount($contactId, 'Sent 1300 Docs in One Month');
                break;
        }
        return $result;
    }

    public function applyOverLimitTag($userId)
    {
        $contactId = $this->userService->getInfusionContactId($userId);
        return $this->infusionSoftService->applyTagToContactInAdminInfusionSoftAccount($contactId, 'Account Overlimit');
    }
}
