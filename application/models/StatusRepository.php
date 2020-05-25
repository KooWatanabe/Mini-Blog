<?php


class StatusRepository extends DbRepository
{
    public function insert($user_id, $body){
        $now = new DateTime();
        $sql = "
            INSERT INTO status(user_id, body, created_at)
                VALUES (:user_id, :body, :created_at)";

        $stmt = $this->execute($sql, array(
            ':user_id' => $user_id,
            ':body'    => $body,
            ':created_at' => $now->format('Y-m-d H:i:s'),
        ));
    }

    public function fetchAllPersonalArchivesByUserId($user_id){
        $sql = "
            SELECT st.*, u.user_name
            FROM status st
            LEFT JOIN user u ON st.user_id = u.id
            LEFT JOIN following fw ON st.user_id = fw.following_id
                AND fw.user_id = :user_id
            WHERE u.id = :user_id OR fw.user_id = :user_id
            ORDER BY st.created_at DESC";

        return $this->fetchAll($sql, array(':user_id' => $user_id));
    }

    public function fetchAllByUserId($user_id){
        $sql = "
            SELECT st.*, u.user_name
            FROM status st
            LEFT JOIN user u ON st.user_id = u.id
            WHERE u.id = :user_id
            ORDER BY st.created_at DESC";

        return $this->fetchAll($sql, array(':user_id' => $user_id));
    }

    public function fetchByIdAndUserName($id, $user_name){
        $sql = "
            SELECT st.*, u.user_name
            FROM status st
            LEFT JOIN user u ON st.user_id = u.id
            WHERE st.id = :id
            AND u.user_name = :user_name";

        return $this->fetch($sql, array('id' => $id, ':user_name' => $user_name));
    }
}