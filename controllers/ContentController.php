<?php

class ContentController extends Controller {
    private $contentModel;

    public function __construct() {
        parent::__construct();
        $this->contentModel = new ContentModel();
    }

    private function sanitizeContent($content) {
        // Replace literal '\n', '\r', '\t' with actual characters
        $content = str_replace(['\n', '\r', '\t'], ["\n", "\r", "\t"], $content);

        // Strip control characters (except \n, \r, \t)
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $content);

        // Strip HTML tags to prevent XSS and keep Markdown clean
        $content = strip_tags($content);

        // Trim and normalize multiple newlines
        $content = trim($content);
        $content = preg_replace("/\n{3,}/", "\n\n", $content);

        return $content;
    }

    private function validateContent($content) {
        $errors = [];
        // Check for literal escape sequences (e.g., \\n)
        if (preg_match('/\\\\[nrt]/', $content)) {
            $errors[] = "Use actual line breaks (Enter key) or tabs instead of typing \\n, \\r, or \\t.";
        }
        // Ensure content isn't just whitespace
        if (empty(trim($content))) {
            $errors[] = "Content cannot be empty or just whitespace.";
        }
        // Check for HTML tags (in case strip_tags missed something)
        if (preg_match('/<[a-zA-Z][^>]*>/', $content)) {
            $errors[] = "HTML tags are not allowed in content. Use Markdown for formatting.";
        }
        return $errors;
    }

    public function favicon() {
        header('HTTP/1.1 404 Not Found');
        exit;
    }

    public function adminDashboard() {
        $postCount = $this->contentModel->getCount('post');
        $pageCount = $this->contentModel->getCount('page');
        $this->render('admin/dashboard', [
            'postCount' => $postCount,
            'pageCount' => $pageCount
        ]);
    }

    public function blog() {
        $posts = $this->contentModel->getPosts();
        $this->render('blog', ['posts' => $posts]);
    }

    public function view($slug) {
        $item = $this->contentModel->getBySlug($slug);
        if ($item && $item['published']) {
            $this->render('content', [
                'item' => $item,
                'meta' => [
                    'title' => $item['meta_title'] ?: $item['title'],
                    'description' => $item['meta_description'],
                    'image' => $item['meta_image'],
                    'url' => $this->getBaseUrl() . '/' . $slug,
                    'type' => $item['type'] === 'post' ? 'article' : 'website'
                ]
            ]);
        } else {
            $this->render('error', ['code' => 404, 'message' => 'Page or post not found']);
        }
    }

    public function adminList() {
        $items = $this->contentModel->getAll();
        $this->render('admin/content_list', ['items' => $items]);
    }

    public function adminAdd() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $content = $_POST['content'] ?? '';
            $type = $_POST['type'] ?? 'post';
            $meta_title = trim($_POST['meta_title'] ?? '');
            $meta_description = trim($_POST['meta_description'] ?? '');
            $meta_image = trim($_POST['meta_image'] ?? '');
            $published = isset($_POST['published']) ? 1 : 0;

            // Sanitize and validate content
            $content = $this->sanitizeContent($content);
            $errors = $this->validateContent($content);

            if (empty($title)) $errors[] = "Title is required";
            if (empty($slug)) $errors[] = "Slug is required";
            elseif ($this->contentModel->slugExists($slug)) $errors[] = "Slug already taken";
            if (!in_array($type, ['page', 'post'])) $errors[] = "Invalid type";

            error_log("adminAdd sanitized content: " . json_encode($content));

            if (empty($errors)) {
                $this->contentModel->add(
                    $title, $slug, $content, $type, $meta_title, $meta_description, $meta_image,
                    $this->getUser()['id'], $published
                );
                $this->redirect('/admin/content');
            } else {
                $this->render('admin/content_form', [
                    'errors' => $errors,
                    'title' => $title,
                    'slug' => $slug,
                    'content' => $content,
                    'type' => $type,
                    'meta_title' => $meta_title,
                    'meta_description' => $meta_description,
                    'meta_image' => $meta_image,
                    'published' => $published
                ]);
            }
        } else {
            $this->render('admin/content_form');
        }
    }

    public function adminEdit($id) {
        $item = $this->contentModel->getById($id);
        if (!$item) {
            $this->render('error', ['code' => 404, 'message' => 'Content not found']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $content = $_POST['content'] ?? '';
            $type = $_POST['type'] ?? 'post';
            $meta_title = trim($_POST['meta_title'] ?? '');
            $meta_description = trim($_POST['meta_description'] ?? '');
            $meta_image = trim($_POST['meta_image'] ?? '');
            $published = isset($_POST['published']) ? 1 : 0;

            // Sanitize and validate content
            $content = $this->sanitizeContent($content);
            $errors = $this->validateContent($content);

            if (empty($title)) $errors[] = "Title is required";
            if (empty($slug)) $errors[] = "Slug is required";
            elseif ($slug !== $item['slug'] && $this->contentModel->slugExists($slug)) $errors[] = "Slug already taken";
            if (!in_array($type, ['page', 'post'])) $errors[] = "Invalid type";

            error_log("adminEdit sanitized content: " . json_encode($content));

            if (empty($errors)) {
                $this->contentModel->update(
                    $id, $title, $slug, $content, $type, $meta_title, $meta_description, $meta_image, $published
                );
                $this->redirect('/admin/content');
            } else {
                $this->render('admin/content_form', [
                    'errors' => $errors,
                    'item' => [
                        'id' => $id,
                        'title' => $title,
                        'slug' => $slug,
                        'content' => $content,
                        'type' => $type,
                        'meta_title' => $meta_title,
                        'meta_description' => $meta_description,
                        'meta_image' => $meta_image,
                        'published' => $published
                    ]
                ]);
            }
        } else {
            $this->render('admin/content_form', ['item' => $item]);
        }
    }

    public function adminDelete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->contentModel->delete($id);
            $this->redirect('/admin/content');
        }
    }
}