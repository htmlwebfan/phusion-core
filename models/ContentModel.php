<?php

class ContentModel extends Model {
    public function getPosts() {
        return $this->db->fetchAll(
            "SELECT * FROM content WHERE type = 'post' AND published = 1 ORDER BY created_at DESC"
        );
    }

    public function getBySlug($slug) {
        return $this->db->fetchOne(
            "SELECT * FROM content WHERE slug = ?",
            [$slug]
        );
    }

    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM content ORDER BY created_at DESC");
    }

    public function getById($id) {
        return $this->db->fetchOne("SELECT * FROM content WHERE id = ?", [$id]);
    }

    public function slugExists($slug) {
        return $this->db->fetchOne("SELECT id FROM content WHERE slug = ?", [$slug]) !== null;
    }

    public function add($title, $slug, $content, $type, $meta_title, $meta_description, $meta_image, $author_id, $published) {
        $this->db->query(
            "INSERT INTO content (title, slug, content, type, meta_title, meta_description, meta_image, author_id, published) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$title, $slug, $content, $type, $meta_title, $meta_description, $meta_image, $author_id, $published]
        );
    }

    public function update($id, $title, $slug, $content, $type, $meta_title, $meta_description, $meta_image, $published) {
        $this->db->query(
            "UPDATE content SET title = ?, slug = ?, content = ?, type = ?, meta_title = ?, meta_description = ?, meta_image = ?, published = ? 
             WHERE id = ?",
            [$title, $slug, $content, $type, $meta_title, $meta_description, $meta_image, $published, $id]
        );
    }

    public function delete($id) {
        $this->db->query("DELETE FROM content WHERE id = ?", [$id]);
    }

    public function getCount($type) {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM content WHERE type = ? AND published = 1",
            [$type]
        );
        return $result['count'] ?? 0;
    }
}