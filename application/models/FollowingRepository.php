<?php


class FollowingRepository extends DbRepository
{
    public function insert($user_id, $following_id){
        $sql = "INSERT INTO following VALUES(:user_id, :follwing_id)";
        $this->execute($sql, array(':user_id' => $user_id, ':following_id' => $following_id,));
    }

    public function isFollowing($user_id, $following_id){
        $sql = "SELECT COUNT(user_id) as count
                FROM following
                WHERE following_id = :following_id
                AND user_id = :user_id";

        $row = $this->fetch($sql, array(':user_id' => $user_id, ':following_id' => $following_id));
        return $row['count'] !== 0;
    }
}