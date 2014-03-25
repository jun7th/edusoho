<?php

namespace Topxia\Service\User\Dao;

interface MemberDao
{
	public function getMemberByUserId($userId);
	
	public function searchMembers($conditions, $orderBy, $start, $limit);

	public function searchMembersCount($conditions);

	public function deleteMember($userId);

	public function addMember($member);

	public function updateMember($id, $fields);
}