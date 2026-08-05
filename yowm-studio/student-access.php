<?php
if ( ! defined( 'ABSPATH' ) ) exit;

final class YOWM_Student_Access {
	const ROLE = 'yowm_student';
	const META_MEMBERSHIPS = '_yowm_cohort_memberships';
	const META_LAST_LOGIN = '_yowm_last_login';
	const META_FEED_PREFIX = '_yowm_personal_podcast_';
	const OPTION_INVITES = 'yowm_pending_student_invitations';

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'ensure_role' ), 2 );
		add_action( 'init', array( __CLASS__, 'maybe_render_personal_feed' ), 0 );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_render_setup' ), 0 );
		add_action( 'admin_post_yowm_add_students', array( __CLASS__, 'handle_add_students' ) );
		add_action( 'admin_post_yowm_save_roster', array( __CLASS__, 'handle_save_roster' ) );
		add_action( 'admin_post_yowm_student_action', array( __CLASS__, 'handle_student_action' ) );
		add_action( 'wp_login', array( __CLASS__, 'record_login' ), 10, 2 );
		add_filter( 'login_redirect', array( __CLASS__, 'login_redirect' ), 10, 3 );
		add_filter( 'registration_errors', array( __CLASS__, 'block_registration' ), 100 );
		add_action( 'admin_init', array( __CLASS__, 'redirect_students_from_admin' ) );
		add_filter( 'show_admin_bar', array( __CLASS__, 'hide_student_admin_bar' ) );
	}

	public static function ensure_role(): void {
		if ( ! get_role( self::ROLE ) ) add_role( self::ROLE, 'YOWM Student', array( 'read' => true ) );
	}

	private static function cohorts(): array {
		return get_posts(array('post_type'=>YOWM_Studio::COHORT,'post_status'=>'any','posts_per_page'=>-1,'orderby'=>'title','order'=>'ASC'));
	}
	private static function memberships(int $user_id): array {
		$v=get_user_meta($user_id,self::META_MEMBERSHIPS,true); return is_array($v)?$v:array();
	}
	public static function status(int $user_id,int $cohort_id): string {
		$m=self::memberships($user_id); $s=isset($m[$cohort_id])?sanitize_key((string)$m[$cohort_id]):'';
		return in_array($s,array('active','revoked'),true)?$s:'';
	}
	private static function set_memberships(int $user_id,array $cohort_ids): void {
		$old=self::memberships($user_id); $new=array();
		foreach($cohort_ids as $id){$id=absint($id); if($id) $new[$id]=isset($old[$id])&&'revoked'===$old[$id]?'revoked':'active';}
		update_user_meta($user_id,self::META_MEMBERSHIPS,$new);
		foreach(array_keys($new) as $id) self::token($user_id,(int)$id,true);
		foreach(array_diff(array_keys($old),array_keys($new)) as $id) delete_user_meta($user_id,self::feed_key((int)$id));
	}
	private static function set_status(int $user_id,int $cohort_id,string $status): void {
		$m=self::memberships($user_id); $m[$cohort_id]='active'===$status?'active':'revoked'; update_user_meta($user_id,self::META_MEMBERSHIPS,$m);
	}
	public static function current_user_can_access(int $cohort_id): bool {
		if(!is_user_logged_in()) return false; $uid=get_current_user_id();
		return user_can($uid,'edit_posts')||'active'===self::status($uid,$cohort_id);
	}

	// A moderator is a student who also holds WordPress's Editor role, granting
	// full editing of all YOWM content while staying a student (feed intact) and
	// staying locked out of Students/Settings/Plugins (those need manage_options).
	public static function is_moderator(int $user_id): bool {
		$u=get_userdata($user_id); return $u&&in_array('editor',(array)$u->roles,true);
	}
	private static function set_moderator(int $user_id,bool $on): void {
		$u=get_userdata($user_id); if(!$u||!in_array(self::ROLE,(array)$u->roles,true)) return;
		if($on) $u->add_role('editor'); else $u->remove_role('editor');
	}

	private static function feed_key(int $cohort_id): string { return self::META_FEED_PREFIX.$cohort_id; }
	private static function token(int $user_id,int $cohort_id,bool $create=true): string {
		$key=self::feed_key($cohort_id); $t=(string)get_user_meta($user_id,$key,true);
		if(!$t&&$create){$t=wp_generate_password(40,false,false);update_user_meta($user_id,$key,$t);} return $t;
	}
	public static function feed_url(int $user_id,int $cohort_id): string {
		if('active'!==self::status($user_id,$cohort_id)&&!user_can($user_id,'edit_posts')) return '';
		$year=YOWM_Studio::cohort_year($cohort_id);$t=self::token($user_id,$cohort_id);
		return $year&&$t?home_url('/podcast/'.$year.'/'.rawurlencode($t).'/'):'';
	}
	public static function current_user_feed_url(int $cohort_id): string { return is_user_logged_in()?self::feed_url(get_current_user_id(),$cohort_id):''; }
	private static function find_feed_user(int $cohort_id,string $token): ?WP_User {
		$users=get_users(array('meta_key'=>self::feed_key($cohort_id),'meta_value'=>$token,'number'=>1));
		return $users&&'active'===self::status($users[0]->ID,$cohort_id)?$users[0]:null;
	}
	public static function maybe_render_personal_feed(): void {
		$route=YOWM_Studio::request_route();$year=absint($route['year']??0);$tok=(string)($route['podcast_token']??'');
		if(!$year||!$tok)return;$cohort=YOWM_Studio::get_cohort_by_year($year);if(!$cohort)return;
		$user=self::find_feed_user($cohort->ID,$tok);if(!$user)return;
		YOWM_Studio::render_podcast_feed($cohort,self::feed_url($user->ID,$cohort->ID));exit;
	}

	private static function invites(): array { $v=get_option(self::OPTION_INVITES,array()); return is_array($v)?$v:array(); }
	private static function save_invites(array $v): void { update_option(self::OPTION_INVITES,$v,false); }
	private static function invitation_by_token(string $raw): array {
		$hash=hash('sha256',$raw); foreach(self::invites() as $id=>$inv){if(hash_equals((string)($inv['token_hash']??''),$hash))return array($id,$inv);} return array('',array());
	}
	private static function setup_url(string $raw): string { return home_url('/student-setup/'.rawurlencode($raw).'/'); }
	private static function send_invitation(string $id): bool {
		$all=self::invites();if(empty($all[$id]))return false;$raw=wp_generate_password(48,false,false);
		$all[$id]['token_hash']=hash('sha256',$raw);$all[$id]['invited_at']=current_time('mysql',true);self::save_invites($all);
		$i=$all[$id];$years=array();foreach((array)$i['cohorts'] as $cid){$y=YOWM_Studio::cohort_year(absint($cid));if($y)$years[]=$y;}
		$subject='Set up your Year of Writing Magically account';
		$message='Hi '.($i['first_name']?:'there').",\n\nYou've been invited to the Year of Writing Magically".(count($years)?' ('.implode(', ',$years).')':'').".\n\nChoose your username and password here:\n".self::setup_url($raw)."\n\nThis link is for you only.\n";
		return wp_mail($i['email'],$subject,$message);
	}

	public static function maybe_render_setup(): void {
		$path=trim((string)wp_parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH),'/');
		if(!preg_match('#(?:^|/)student-setup/([A-Za-z0-9]+)/?$#',$path,$m))return;
		$raw=sanitize_text_field($m[1]);list($id,$invite)=self::invitation_by_token($raw);$errors=array();
		if(!$id){self::setup_page(array(),'This invitation link is invalid or has already been used.');exit;}
		if('POST'===$_SERVER['REQUEST_METHOD']){
			$username=sanitize_user((string)wp_unslash($_POST['username']??''),true);$pass=(string)wp_unslash($_POST['password']??'');$confirm=(string)wp_unslash($_POST['confirm_password']??'');
			if(!$username||strlen($username)<3)$errors[]='Choose a username with at least three characters.';
			if(username_exists($username))$errors[]='That username is already taken.';
			if(strlen($pass)<8)$errors[]='Choose a password with at least eight characters.';
			if($pass!==$confirm)$errors[]='The passwords do not match.';
			if(email_exists($invite['email']))$errors[]='An account already exists for this email address. Contact Lani for help.';
			if(!$errors){
				$uid=wp_insert_user(array('user_login'=>$username,'user_email'=>$invite['email'],'user_pass'=>$pass,'first_name'=>$invite['first_name'],'last_name'=>$invite['last_name'],'display_name'=>trim($invite['first_name'].' '.$invite['last_name'])?:$username,'role'=>self::ROLE));
				if(is_wp_error($uid))$errors[]=$uid->get_error_message(); else {
					self::set_memberships($uid,(array)$invite['cohorts']);$all=self::invites();unset($all[$id]);self::save_invites($all);wp_set_current_user($uid);wp_set_auth_cookie($uid,true);
					wp_safe_redirect(self::student_home_url($uid));exit;
				}
			}
		}
		self::setup_page($invite,'',$errors);exit;
	}
	private static function setup_page(array $invite,string $message='',array $errors=array()): void {
		status_header($message?410:200);nocache_headers();$name=trim(($invite['first_name']??'').' '.($invite['last_name']??''));
		?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Set up your classroom account</title>
		<style>body{margin:0;background:#ffeedb;color:#4a4063;font:18px/1.55 Georgia,serif}.box{width:min(560px,calc(100% - 32px));margin:7vh auto;padding:32px;background:#fff8ef;border:1px solid #d8ccd0;border-radius:18px}h1{font-size:42px;line-height:1;margin:.2em 0}.field{margin:18px 0}label{display:block;font:700 13px Arial,sans-serif;margin-bottom:6px}input{box-sizing:border-box;width:100%;padding:12px;font-size:17px}.button{width:100%;padding:13px;background:#4a4063;color:white;border:0;border-radius:8px;font-weight:bold}.error{background:#fff0f0;border-left:4px solid #b32d2e;padding:10px 14px}</style></head><body><main class="box"><p>THE YEAR OF WRITING MAGICALLY</p><h1>Set up your classroom account</h1>
		<?php if($message):?><p class="error"><?php echo esc_html($message);?></p><?php else:?>
		<p>Hi<?php echo $name?', '.esc_html($name):'';?>. Choose the username and password you'll use for the classroom.</p><?php foreach($errors as $e):?><p class="error"><?php echo esc_html($e);?></p><?php endforeach;?>
		<form method="post"><div class="field"><label>Email</label><input value="<?php echo esc_attr($invite['email']);?>" disabled></div><div class="field"><label for="username">Choose a username</label><input id="username" name="username" autocomplete="username" required></div><div class="field"><label for="password">Choose a password</label><input id="password" type="password" name="password" autocomplete="new-password" required></div><div class="field"><label for="confirm">Confirm password</label><input id="confirm" type="password" name="confirm_password" autocomplete="new-password" required></div><button class="button">Create my account</button></form><?php endif;?></main></body></html><?php
	}

	private static function all_students(): array { return get_users(array('role'=>self::ROLE,'orderby'=>'display_name','order'=>'ASC','number'=>999)); }
	private static function cohort_label_list(array $ids): string {$out=array();foreach($ids as $id){$y=YOWM_Studio::cohort_year(absint($id));if($y)$out[]=$y;}return implode(', ',$out);}
	private static function student_action_url(string $do,array $args): string {
		$args=array_merge(array('action'=>'yowm_student_action','do'=>$do),$args);$nonce='yowm_student_'.$do.'_'.md5(wp_json_encode($args));
		return wp_nonce_url(add_query_arg($args,admin_url('admin-post.php')),$nonce);
	}
	private static function verify_action_nonce(string $do,array $args): void {$nonce='yowm_student_'.$do.'_'.md5(wp_json_encode(array_merge(array('action'=>'yowm_student_action','do'=>$do),$args)));check_admin_referer($nonce);}

	public static function handle_add_students(): void {
		if(!current_user_can('manage_options'))wp_die('Permission denied.');check_admin_referer('yowm_add_students');
		$first=sanitize_text_field(wp_unslash($_POST['first_name']??''));$last=sanitize_text_field(wp_unslash($_POST['last_name']??''));$email=sanitize_email(wp_unslash($_POST['email']??''));$cohorts=array_map('absint',(array)($_POST['cohorts']??array()));
		if(!is_email($email)||!$cohorts){self::redirect_admin('Enter a valid email and select at least one cohort.','error');}
		$user=get_user_by('email',$email);
		if($user){if(!in_array(self::ROLE,(array)$user->roles,true)){self::redirect_admin('That email belongs to a non-student WordPress account and was not changed.','error');}
			$m=array_unique(array_merge(array_keys(self::memberships($user->ID)),$cohorts));self::set_memberships($user->ID,$m);self::redirect_admin('Existing student updated.');}
		$all=self::invites();$existing='';foreach($all as $id=>$i){if(strtolower($i['email'])===strtolower($email)){$existing=$id;break;}}
		$id=$existing?:wp_generate_uuid4();$old=$existing?$all[$id]:array('cohorts'=>array());$all[$id]=array('first_name'=>$first,'last_name'=>$last,'email'=>$email,'cohorts'=>array_values(array_unique(array_merge((array)$old['cohorts'],$cohorts))),'token_hash'=>'','created_at'=>$old['created_at']??current_time('mysql',true));self::save_invites($all);
		$sent=self::send_invitation($id);self::redirect_admin($sent?'Invitation sent.':'Invitation saved, but WordPress could not send the email.');
	}
	public static function handle_save_roster(): void {
		if(!current_user_can('manage_options'))wp_die('Permission denied.');check_admin_referer('yowm_save_roster');
		foreach((array)($_POST['students']??array()) as $uid=>$row){$uid=absint($uid);$u=get_userdata($uid);if(!$u||!in_array(self::ROLE,(array)$u->roles,true))continue;wp_update_user(array('ID'=>$uid,'first_name'=>sanitize_text_field(wp_unslash($row['first_name']??'')),'last_name'=>sanitize_text_field(wp_unslash($row['last_name']??'')),'display_name'=>trim(sanitize_text_field(wp_unslash($row['first_name']??'')).' '.sanitize_text_field(wp_unslash($row['last_name']??'')))?:$u->user_login));self::set_memberships($uid,array_map('absint',(array)($row['cohorts']??array())));self::set_moderator($uid,!empty($row['moderator']));}
		self::redirect_admin('Roster changes saved.');
	}
	public static function handle_student_action(): void {
		if(!current_user_can('manage_options'))wp_die('Permission denied.');$do=sanitize_key(wp_unslash($_GET['do']??''));$uid=absint($_GET['user_id']??0);$iid=sanitize_text_field(wp_unslash($_GET['invite_id']??''));$cid=absint($_GET['cohort_id']??0);$args=array();if($uid)$args['user_id']=$uid;if($iid)$args['invite_id']=$iid;if($cid)$args['cohort_id']=$cid;self::verify_action_nonce($do,$args);
		if($iid){$all=self::invites();if('delete_invite'===$do)unset($all[$iid]);elseif('resend_invite'===$do){self::send_invitation($iid);self::redirect_admin('Invitation resent.');}self::save_invites($all);self::redirect_admin('Invitation deleted.');}
		$u=get_userdata($uid);if(!$u||!in_array(self::ROLE,(array)$u->roles,true))wp_die('This account cannot be managed here.');
		if('delete_user'===$do){require_once ABSPATH.'wp-admin/includes/user.php';wp_delete_user($uid);self::redirect_admin('Student account permanently deleted.');}
		if('suspend'===$do){$m=self::memberships($uid);foreach($m as $id=>$v){$m[$id]='revoked';delete_user_meta($uid,self::feed_key((int)$id));}update_user_meta($uid,self::META_MEMBERSHIPS,$m);}
		elseif('restore_all'===$do){$m=self::memberships($uid);foreach($m as $id=>$v){$m[$id]='active';self::token($uid,(int)$id,true);}update_user_meta($uid,self::META_MEMBERSHIPS,$m);}
		elseif('rotate'===$do&&$cid){update_user_meta($uid,self::feed_key($cid),wp_generate_password(40,false,false));}
		self::redirect_admin('Student access updated.');
	}
	private static function redirect_admin(string $msg,string $type='success'): void {wp_safe_redirect(add_query_arg(array('page'=>'yowm-student-access','yowm_message'=>$msg,'yowm_type'=>$type),admin_url('admin.php')));exit;}

	public static function record_login(string $login,WP_User $user): void {update_user_meta($user->ID,self::META_LAST_LOGIN,current_time('mysql',true));}
	public static function student_home_url(int $uid): string {foreach(self::memberships($uid) as $cid=>$s){if('active'===$s){$y=YOWM_Studio::cohort_year((int)$cid);if($y)return home_url('/'.$y.'/');}}return home_url('/');}
	public static function login_redirect(string $redirect,string $requested,$user): string {if(is_wp_error($user)||!$user instanceof WP_User)return $redirect;if(in_array(self::ROLE,(array)$user->roles,true)&&!user_can($user,'edit_posts'))return ($requested&&!str_contains($requested,'/wp-admin'))?wp_validate_redirect($requested,self::student_home_url($user->ID)):self::student_home_url($user->ID);return $requested?wp_validate_redirect($requested,$redirect):$redirect;}
	public static function block_registration(WP_Error $e): WP_Error {if(!current_user_can('manage_options'))$e->add('yowm_invite_only','Classroom accounts are invitation-only.');return $e;}
	public static function redirect_students_from_admin(): void {if(!is_user_logged_in()||wp_doing_ajax())return;$u=wp_get_current_user();if(in_array(self::ROLE,(array)$u->roles,true)&&!current_user_can('edit_posts')){wp_safe_redirect(self::student_home_url($u->ID));exit;}}
	public static function hide_student_admin_bar(bool $show): bool {if(!is_user_logged_in())return $show;$u=wp_get_current_user();return in_array(self::ROLE,(array)$u->roles,true)&&!current_user_can('edit_posts')?false:$show;}

	public static function admin_page(): void {
		if(!current_user_can('manage_options'))return;$cohorts=self::cohorts();$students=self::all_students();$invites=self::invites();
		echo '<div class="wrap yowm-admin"><h1>Students</h1><p>One roster for every current, returning, and invited student.</p>';
		if(isset($_GET['yowm_message']))echo '<div class="notice notice-'.esc_attr('error'===($_GET['yowm_type']??'')?'error':'success').' is-dismissible"><p>'.esc_html(sanitize_text_field(wp_unslash($_GET['yowm_message']))).'</p></div>';
		echo '<section class="yowm-access-panel"><h2>Invite a student</h2><form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';wp_nonce_field('yowm_add_students');echo '<input type="hidden" name="action" value="yowm_add_students"><div class="yowm-invite-fields"><p><label>First name<input name="first_name" required></label></p><p><label>Last name<input name="last_name" required></label></p><p><label>Email<input type="email" name="email" required></label></p><fieldset><legend>Cohort year(s)</legend>';
		foreach($cohorts as $c){echo '<label><input type="checkbox" name="cohorts[]" value="'.esc_attr((string)$c->ID).'"> '.esc_html((string)YOWM_Studio::cohort_year($c->ID)).'</label> ';}echo '</fieldset></div><p><button class="button button-primary">Send invitation</button></p></form></section>';
		echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';wp_nonce_field('yowm_save_roster');echo '<input type="hidden" name="action" value="yowm_save_roster"><table class="widefat striped yowm-unified-roster"><thead><tr><th>Username</th><th>First name</th><th>Last name</th><th>Email</th><th>Cohort years</th><th>Status</th><th>Moderator</th><th>Podcast feeds</th><th>Actions</th></tr></thead><tbody>';
		foreach($students as $u){$m=self::memberships($u->ID);$active=array_filter($m,fn($v)=>'active'===$v);$rev=array_filter($m,fn($v)=>'revoked'===$v);echo '<tr><td><strong>'.esc_html($u->user_login).'</strong></td><td><input name="students['.$u->ID.'][first_name]" value="'.esc_attr($u->first_name).'"></td><td><input name="students['.$u->ID.'][last_name]" value="'.esc_attr($u->last_name).'"></td><td>'.esc_html($u->user_email).'</td><td class="yowm-cohort-checks">';foreach($cohorts as $c){echo '<label><input type="checkbox" name="students['.$u->ID.'][cohorts][]" value="'.$c->ID.'" '.checked(isset($m[$c->ID]),true,false).'> '.esc_html((string)YOWM_Studio::cohort_year($c->ID)).'</label> ';}echo '</td><td>'.($rev?'Partly/fully revoked':(get_user_meta($u->ID,self::META_LAST_LOGIN,true)?'Active':'Account created')).'</td><td><label class="yowm-mod-check"><input type="checkbox" name="students['.$u->ID.'][moderator]" '.checked(self::is_moderator($u->ID),true,false).'> Can edit</label></td><td>';
		foreach($active as $cid=>$v){$url=self::feed_url($u->ID,(int)$cid);echo '<div><strong>'.esc_html((string)YOWM_Studio::cohort_year((int)$cid)).':</strong> <input class="code" readonly value="'.esc_attr($url).'"> <a href="'.esc_url(self::student_action_url('rotate',array('user_id'=>$u->ID,'cohort_id'=>(int)$cid))).'">New URL</a></div>';}echo '</td><td>';
		if($rev)echo '<a href="'.esc_url(self::student_action_url('restore_all',array('user_id'=>$u->ID))).'">Restore all</a> · ';else echo '<a href="'.esc_url(self::student_action_url('suspend',array('user_id'=>$u->ID))).'">Suspend</a> · ';
		echo '<a class="yowm-danger-link" onclick="return confirm(\'Permanently delete this student account and every cohort membership? This cannot be undone.\')" href="'.esc_url(self::student_action_url('delete_user',array('user_id'=>$u->ID))).'">Delete permanently</a></td></tr>';}
		foreach($invites as $id=>$i){echo '<tr class="yowm-pending"><td><em>Not chosen</em></td><td>'.esc_html($i['first_name']).'</td><td>'.esc_html($i['last_name']).'</td><td>'.esc_html($i['email']).'</td><td>'.esc_html(self::cohort_label_list((array)$i['cohorts'])).'</td><td>Invitation pending</td><td>—</td><td>Created after activation</td><td><a href="'.esc_url(self::student_action_url('resend_invite',array('invite_id'=>$id))).'">Resend</a> · <a class="yowm-danger-link" onclick="return confirm(\'Delete this pending invitation?\')" href="'.esc_url(self::student_action_url('delete_invite',array('invite_id'=>$id))).'">Delete</a></td></tr>';}
		echo '</tbody></table><p><button class="button button-primary">Save roster changes</button></p></form></div>';
	}
}
