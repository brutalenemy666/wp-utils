<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * $file_url = 'http://example.com/my_photo.png';
 * $file_url = 'http://example.com/my_pdf_file.pdf';
 * ...
 * $attachment_id = Crb_File_To_Media::add( $file_url ); // return  attachment ID if exists otherwise uploads it.
 * $attachment_id = Crb_File_To_Media::add( $file_url, true ); // uploads it again, doesn't overwrite.
 */
class Crb_File_To_Media {

	protected $file_url;

	protected $file_data = false;

	protected $attachment_id = 0;

	private function __construct() {}

	public static function add( $file_url='', $force_upload=false ) {
		$uploader = new self();
		$uploader->file_url = $file_url;

		$already_uploaded = $uploader->attachment_exists();
		if ( $already_uploaded && $force_upload===false ) {
			return $already_uploaded;
		}

		$uploader->upload();

		if ( !$uploader->file_data ) {
			return false;
		}

		$uploader->add_to_media_library();

		return $uploader->attachment_id;
	}

	protected function upload() {
		$upload_dir = wp_upload_dir();

		$file_name = trim(uniqid() . '-' . basename($this->file_url));
		$ext = pathinfo($file_name, PATHINFO_EXTENSION);

		$file_name = rtrim($file_name, '.' . $ext);
		$file_name = sanitize_title($file_name) . '.' . $ext;

		$directory_to_save = $upload_dir['path'] . DIRECTORY_SEPARATOR;

		// ---->
		$ch = curl_init($this->file_url);
		$fp = fopen($directory_to_save . $file_name, "wb");

		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);

		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		// ---<

		$filetype = wp_check_filetype($directory_to_save . $file_name);

		$this->file_data = array(
			'filename' => $file_name,
			'abs_path' => $upload_dir['path'] . DIRECTORY_SEPARATOR . $file_name,
			'url' => $upload_dir['url'] . '/' .  $file_name,
			'type' => $filetype['type'],
			'ext' => $filetype['ext'],
			'attach_file' => preg_replace('~^(/)~', '', $upload_dir['subdir'] . '/' .  $file_name)
		);

		return $this;
	}

	protected function add_to_media_library() {
		if ( !$this->file_data ) {
			return $this;
		}

		$attachment = array(
			'guid' => str_replace('\\', '/', $this->file_data['url']),
			'post_mime_type' => $this->file_data['type'],
			'post_title' => $this->file_data['filename'],
			'post_content' => '',
			'post_status' => 'inherit'
		);

		$attach_id = wp_insert_attachment($attachment);

		// you must first include the image.php file
		// for the function wp_generate_attachment_metadata() to work
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');

		// required for wp_read_video_metadata()
		require_once(ABSPATH . 'wp-admin/includes/media.php');

		// update atatchment meta data
		$attach_data = wp_generate_attachment_metadata($attach_id, $this->file_data['abs_path']);
		wp_update_attachment_metadata($attach_id, $attach_data);

		update_post_meta($attach_id, '_wp_attached_file', $this->file_data['attach_file']);
		update_post_meta($attach_id, '_crb_attachment_origin_url', $this->file_url);

		$this->attachment_id = $attach_id;

		return $this;
	}

	protected function attachment_exists() {
		global $wpdb;

		$query = "SELECT post.ID FROM {$wpdb->posts} AS post
				INNER JOIN {$wpdb->postmeta} AS meta ON meta.post_id = post.ID
				WHERE post.post_type = 'attachment'
				AND meta.meta_key = '_crb_attachment_origin_url'
				AND meta.meta_value = %s";
		$query = $wpdb->prepare($query, $uploader->file_url);

		return $wpdb->get_var($query);
	}
}
