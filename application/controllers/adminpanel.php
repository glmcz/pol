<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Adminpanel extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
        
        $this->load->library(array('image_CRUD','grocery_CRUD'));
	}
	
	function _my_output($output = null)
	{
       $name = 'main';

       $this->display_lib->admin_page($output ,$name); 		
	}
	
	function index()
	{
	    if ($this->dx_auth->is_role(array('admin','moderator','author')))  
        {
    		$this->_my_output((object)array('output' => '' , 'js_files' => array() , 'css_files' => array()));
        
        }else{
            
            redirect('auth/login');
        }
	}	
    
   	function pages()
	{
	    if ($this->dx_auth->is_role(array('admin','moderator')))  
        {
    	    
            $c = new grocery_CRUD();
            
            if ($this->dx_auth->is_role('moderator')){
                $c->unset_add();
                $c->unset_edit();
                $c->unset_delete();
            }
            
            $c->unset_read();
            
            if ($this->dx_auth->is_role('admin')){
                $c->add_action('CZ', '', 'adminpanel/content/pages/cz','ui-icon-plus');
            }
            
            $c->set_theme('datatables'); 
            $c->set_table('pages');
            $c->columns('page_id','url','title');
            $c->fields('page_id','route_id','url','title','wp');
            $c->set_subject('страницу');
            $c->required_fields('url');
            
            $c->display_as('page_id','#')
              ->display_as('url','URL')
              ->display_as('title','Заголовок')
              ->display_as('wp','Главная');
              
            $c->field_type('wp','invisible');
            $c->field_type('route_id', 'hidden');
              
            $c->callback_before_insert(array($this, 'add_page_valid_url'));
            $c->callback_before_update(array($this, 'update_page_valid_url'));
            $c->callback_before_delete(array($this, 'delete_page_url'));
                 
            $output = $c->render();
           
            $this->_my_output($output);
        
        }else{
            
            redirect('auth/login');
        }

	}
    
    function content($table, $lang, $fid)
	{
        if ($this->dx_auth->is_role(array('admin','moderator','author')))  
        {
    	    
            $c = new grocery_CRUD();
            
            if ($this->dx_auth->is_role(array('moderator','author'))){
                //$c->unset_add();
                //$c->unset_edit();
                $c->unset_delete();
            }
            
            $c->unset_read();

            $c->set_theme('datatables'); 
            $c->set_table('content');
            
            $c->unset_texteditor(array('anons','video'));
            
            $c->where('table',$table);
            $c->where('language',$lang);
            $c->where('fid',$fid);
    
            $c->columns('content_id','title','text');
            $c->fields('title','seo_title','keywords','description','anons','text','table','fid','language','user_add','user_update');
            $c->set_subject('контент');
            $c->required_fields();
            
            $c->display_as('content_id','#')
              ->display_as('title','Заголовок')
              ->display_as('seo_title','SEO заголовок')
              ->display_as('keywords','Ключевики')
              ->display_as('description','Описание')
              ->display_as('anons','Анонс')
              ->display_as('text','Текст')
              ->display_as('video','Видео');

            $c->field_type('table','hidden',$table);
            $c->field_type('fid','hidden',$fid);
            $c->field_type('language','hidden',$lang);
            
            $state = $this->grocery_crud->getState();
            
            if($state == 'add'){
                $c->change_field_type('user_update','hidden');
                $c->change_field_type('user_add', 'hidden', $this->dx_auth->get_user_id());  
            }elseif($state == 'edit'){
               $c->change_field_type('user_add','hidden');
               $c->change_field_type('user_update', 'hidden', $this->dx_auth->get_user_id());  
            }
                 
    	    $output = $c->render();
    			
    	    $this->_my_output($output);
         
        }else{
            
            redirect('auth/login');
        }
	
    }
    
   	function sections($parent_id = 0)
	{
	    if ($this->dx_auth->is_role(array('admin','moderator')))  
        {
    	    
            $c = new grocery_CRUD();
            
            $c->add_action('CZ', '', 'adminpanel/content/sections/cz','ui-icon-plus');
            $c->add_action('Подкатегории', '', 'adminpanel/sections','ui-icon-plus');
            
            $c->unset_read();
            
            if ($this->dx_auth->is_role('moderator')){
                $c->unset_add();
                //$c->unset_edit();
                $c->unset_delete();
            }
            
            $c->where('parent_id',$parent_id);
            
            $c->set_theme('datatables'); 
            $c->set_table('sections');
            
            $c->columns('sequence','url','title','type');
            $c->set_subject('категорию');
            $c->required_fields('title','type');
            //$c->set_rules('url','URL','required|alpha_dash');
            $c->set_relation('type','sections_types','name');
            
            $c->field_type('route_id', 'hidden');
            
            $c->display_as('section_id','#')
              ->display_as('url','URL')
              ->display_as('title','Заголовок')
              ->display_as('keywords','Ключевики')
              ->display_as('description','Описание')
              ->display_as('text','Текст')
              ->display_as('img_url','Картинка')
              ->display_as('count_views','Просмотров')
              ->display_as('sequence','Порядок')
              ->display_as('type','Тип')
              ->display_as('mp','Лента на главной')
              ->display_as('show','Выводить')
              ->display_as('show_latest_news','Свежие новости на главной');
              
            $c->set_field_upload('img_url', "assets/uploads/images/sections/");
            
            $c->change_field_type('parent_id', 'hidden', $parent_id);  
            
            $c->callback_after_upload(array($this,'callback_section_after_upload'));
           
            $c->callback_before_insert(array($this, 'add_section_valid_url'));
            $c->callback_before_update(array($this, 'update_section_valid_url'));
            $c->callback_before_delete(array($this, 'delete_section_url'));
                 
    	    $output = $c->render();
    			
    	    $this->_my_output($output);
         
        }else{
            
            redirect('auth/login');
        }
	
    }
    
   	function sections_types()
	{
	    if ($this->dx_auth->is_role('admin'))  
        {
    	    
            $c = new grocery_CRUD();
            
            $c->unset_read();
            
            $c->set_theme('datatables'); 
            $c->set_table('sections_types');
    
            $c->columns('sections_types_id','abb','name');
            $c->set_subject('тип раздела');
            $c->required_fields('abb','name');
            //$c->set_rules('url','URL','required|alpha_dash');
            
            $c->display_as('sections_types_id','#')
              ->display_as('abb','Аббревиа')
              ->display_as('name','Название');
                 
    	    $output = $c->render();
    			
    	    $this->_my_output($output);
         
        }else{
            
            redirect('auth/login');
        }
	
    }
    
    function materials()
	{
	    if ($this->dx_auth->is_role(array('admin','moderator','author')))  
        {
	    
            $c = new grocery_CRUD();
            
            $c->add_action('CZ', '', 'adminpanel/content/materials/cz','ui-icon-plus');
            
            if ($this->dx_auth->is_role(array('admin','moderator'))) {
                $c->add_action('Photos', '', 'adminpanel/photos','ui-icon-plus');
                $c->add_action('Audio', '', 'adminpanel/audio','ui-icon-plus');
            }
            
            $c->unset_read();
            
            if ($this->dx_auth->is_role('author')){
                //$c->unset_add();
                $c->unset_edit();
                $c->unset_delete();
            }
            
            $c->set_theme('datatables'); 
            $c->set_table('materials');
            $c->columns('material_id','title','date','show');
            $c->set_relation('section','sections','title');
            $c->set_relation('author_id','authors','name');
            $c->set_subject('материал');
            $c->fields('title','url','section','img_url','video_url','date','route_id','user_add','user_update','author_id','show');
            $c->required_fields('title','section','date');
            //$c->set_rules('url','URL','required|alpha_dash');
            $c->unset_texteditor(array('anons','video_url'));
            
            $c->display_as('material_id','#')
              ->display_as('url','URL')
              ->display_as('title','Заголовок')
              ->display_as('description','Описание')
              ->display_as('keywords','Ключевики')
              ->display_as('anons','Анонс')
              ->display_as('text','Текст')
              ->display_as('img_url','Картинка')
              ->display_as('video_url','Видео')
              ->display_as('date','Дата добавления')
              ->display_as('section','Раздел')
              ->display_as('count_views','Просмотров')
              ->display_as('user_add','Автор')
              ->display_as('user_update','Редактор')
              ->display_as('author_id','Автор')
              ->display_as('show','Публиковать');
            
            $c->change_field_type('route_id','hidden');
            $c->field_type('count_views','readonly');
            
            $state = $this->grocery_crud->getState();
            
            if($state == 'add'){
                $c->change_field_type('user_update','hidden');
                $c->change_field_type('user_add', 'hidden', $this->dx_auth->get_user_id());  
            }elseif($state == 'edit'){
               $c->change_field_type('user_add','hidden');
               $c->change_field_type('user_update', 'hidden', $this->dx_auth->get_user_id());  
            }
            
            $c->set_field_upload('img_url', 'assets/uploads/images/materials/');
            $c->callback_after_upload(array($this,'callback_material_after_upload'));
           
            $c->callback_before_insert(array($this, 'add_material_valid_url'));
            $c->callback_before_update(array($this, 'update_material_valid_url'));
            $c->callback_before_delete(array($this, 'delete_material_url'));
            //$c->callback_before_delete(array($this, 'delete_content'));
            
            $c->callback_column('date',array($this,'_callback_date'));
                 
    	    $output = $c->render();
           
            $this->_my_output($output);
        
        }else{
            
            redirect('auth/login');
        }

	}
    
    function audio($material_id)
	{
	    if ($this->dx_auth->is_role(array('admin','moderator','author')))  
        {
	    
            $c = new grocery_CRUD();
            
            if ($this->dx_auth->is_role('author')){
                $c->unset_add();
                $c->unset_edit();
                $c->unset_delete();
            }
            
            $c->unset_read();
            
            $c->set_theme('datatables'); 
            $c->set_table('audios');
            $c->where('material',$material_id);
            $c->columns('sequence','title');
            $c->set_subject('аудио');
            $c->required_fields('title','audio_url');
            
            $c->display_as('sequence','№')
              ->display_as('title','Название')
              ->display_as('audio_url','Аудио (mp3)')
              ->display_as('user_add','Автор')
              ->display_as('user_update','Редактор');
              
            $c->change_field_type('material', 'hidden', $material_id); 
            
            $state = $this->grocery_crud->getState();
            
            if($state == 'add'){
                $c->change_field_type('user_update','hidden');
                $c->change_field_type('user_add', 'hidden', $this->dx_auth->get_user_id());  
            }elseif($state == 'edit'){
               $c->change_field_type('user_add','hidden');
               $c->change_field_type('user_update', 'hidden', $this->dx_auth->get_user_id());  
            }
            
            $c->set_field_upload('audio_url', 'assets/uploads/audio/');
                 
    	    $output = $c->render();
           
            $this->_my_output($output);
        
        }else{
            
            redirect('auth/login');
        }
        
	}
    
    function newspapers()
	{
	    if ($this->dx_auth->is_role(array('admin','moderator','author')))  
        {
	    
            $c = new grocery_CRUD();
            
            if ($this->dx_auth->is_role('author')){
                $c->unset_add();
                $c->unset_edit();
                $c->unset_delete();
            }else{
                $c->unset_read();
            }
            
            $c->set_theme('datatables'); 
            $c->set_table('newspapers');
            $c->columns('newspaper_id','title','img_url','date');
            $c->set_subject('газету');
            $c->required_fields('title','img_url','pdf_file','date');
            
            $c->display_as('newspaper_id','№')
              ->display_as('title','Название выпуска')
              ->display_as('img_url','Картинка')
              ->display_as('pdf_file','PDF файл')
              ->display_as('date','Дата');

            /*
            $state = $this->grocery_crud->getState();
            
            if($state == 'add'){
                $c->change_field_type('user_update','hidden');
                $c->change_field_type('user_add', 'hidden', $this->dx_auth->get_user_id());  
            }elseif($state == 'edit'){
               $c->change_field_type('user_add','hidden');
               $c->change_field_type('user_update', 'hidden', $this->dx_auth->get_user_id());  
            }
            */
            $c->set_field_upload('img_url', 'assets/uploads/images/newspapers/');
            $c->set_field_upload('pdf_file', 'assets/uploads/files/newspapers/');
            
            $c->callback_after_upload(array($this,'callback_newspapers_after_upload'));
                 
    	    $output = $c->render();
           
            $this->_my_output($output);
        
        }else{
            
            redirect('auth/login');
        }
        
	}
    
    function authors()
	{
	    if ($this->dx_auth->is_role(array('admin','moderator'))) 
        {

            $c = new grocery_CRUD();

            $c->unset_read();

            $c->set_theme('datatables');
            $c->set_table('authors');
            $c->columns('author_id','name');
            $c->set_subject('автора');
            $c->fields('name');
            $c->required_fields('name');

            $c->display_as('author_id','ID')
              ->display_as('name','Имя автора');

			$output = $c->render();

            $this->_my_output($output);

        }else{

            redirect('auth/login');
        }

	}
    
    function friends_edit()
	{
	    if ($this->dx_auth->is_role(array('admin','moderator')))  
        {
	    
            $c = new grocery_CRUD();
            
            $c->unset_read();
            
            $c->set_theme('datatables'); 
            $c->set_table('friends');
            $c->columns('priority','title_cz','img_url','link');
            $c->fields('title_cz','img_url','link','priority');
            $c->set_subject('друга');
            $c->required_fields('title_cz','img_url');
            
            $c->display_as('friend_id','#')
              ->display_as('title_cz','Заголовок')
              ->display_as('img_url','Картинка')
              ->display_as('link','Ссылка')
              ->display_as('priority','Очередность');
    
           $c->set_field_upload('img_url', 'assets/uploads/images/friends/');
           $c->callback_after_upload(array($this,'callback_friends_after_upload'));
                 
    	   $output = $c->render();
           
           $this->_my_output($output);
       
       }else{
            
            redirect('auth/login');
       }

	}
    
    function modules()
	{
	    if ($this->dx_auth->is_role('admin'))  
        {
	    
            $c = new grocery_CRUD();
            
            $c->unset_read();
            
            $c->set_theme('datatables'); 
            $c->set_table('modules');
            $c->columns('priority','title','text');
            $c->set_subject('модуль');
            $c->required_fields('priority','text');
            $c->unset_texteditor(array('text'));
            
            $c->display_as('module_id','#')
              ->display_as('title','Заголовок')  
              ->display_as('text','Код')
              ->display_as('priority','Очередность');
                 
    	   $output = $c->render();
           
           $this->_my_output($output);
        
        }else{
            
            redirect('auth/login');
        }

	}
    
    function banners()
	{
    	if ($this->dx_auth->is_role(array('admin','moderator')))  
        {
            
            $c = new grocery_CRUD();
            
            $c->set_theme('datatables'); 
            $c->set_table('banners');
            
            $c->unset_read();
            
            
            $c->columns('priority','title_ru','img_url','position','lang');
            $c->set_subject('баннер');
            $c->required_fields('title','img_url');
            
            $c->set_relation('position','positions','title');
            $c->set_relation('lang','languages','title');
            
            $c->display_as('banner_id','#')
              ->display_as('title_ru','Заголовок (RU)')
              ->display_as('title_ua','Заголовок (UA)')
              ->display_as('title_en','Заголовок (EN)')
              ->display_as('title_cz','Заголовок (CZ)')
              ->display_as('img_url','Картинка')
              ->display_as('link','Ссылка')
              ->display_as('position','Позиция')
              ->display_as('priority','Очередность')
              ->display_as('lang','Язык');
    
           $c->set_field_upload('img_url', 'assets/uploads/images/banners/');
           //$c->callback_after_upload(array($this,'callback_banners_after_upload'));
                 
    	   $output = $c->render();
           
           $this->_my_output($output);
       
       }else{
            
            redirect('auth/login');
       }

	}
    
    function farewells()
	{
    	if ($this->dx_auth->is_role(array('admin','moderator')))  
        {
            
            $c = new grocery_CRUD();
            
            $c->set_theme('datatables'); 
            $c->set_table('farewells');
            
            $c->unset_read();
            
            $c->columns('farewell_id','text_ru','text_cz','date');
            $c->set_subject('напутствие');
            $c->required_fields('text_ru','date');
            
            $c->display_as('farewell_id','№')
              ->display_as('text_ru','Текст (RU)')
              ->display_as('text_ua','Текст (UA)')
              ->display_as('text_en','Текст (EN)')
              ->display_as('text_cz','Текст (CZ)')
              ->display_as('date','Дата вывода');
              
            $c->unset_texteditor(array('text_ru','text_ua','text_en','text_cz'));
                 
            $output = $c->render();
           
            $this->_my_output($output);
       
       }else{
            
            redirect('auth/login');
       }

	}
    
    function events()
	{
	    if ($this->dx_auth->is_role(array('admin','moderator','author')))  
        {
	    
            $c = new grocery_CRUD();
            
            $c->add_action('CZ', '', 'adminpanel/content/events/cz','ui-icon-plus');
            
            $c->unset_read();
            
            if ($this->dx_auth->is_role('author')){
                //$c->unset_add();
                $c->unset_edit();
                $c->unset_delete();
            }
            
            $c->set_theme('datatables'); 
            $c->set_table('events');
            $c->columns('event_id','title','date','show');
            $c->set_subject('событие');
            $c->fields('title','url','img_url','video_url','date','map','route_id','user_add','user_update','show');
            $c->required_fields('title','date');
            //$c->set_rules('url','URL','required|alpha_dash');
            $c->unset_texteditor(array('video_url','map'));
            
            $c->display_as('event_id','#')
              ->display_as('url','URL')
              ->display_as('title','Заголовок')
              ->display_as('img_url','Картинка')
              ->display_as('video_url','Видео')
              ->display_as('date','Дата события')
              ->display_as('map','Место события')
              ->display_as('count_views','Просмотров')
              ->display_as('user_add','Автор')
              ->display_as('user_update','Редактор')
              ->display_as('show','Публиковать');
            
            $c->change_field_type('route_id','hidden');
            $c->field_type('count_views','readonly');
            
            $state = $this->grocery_crud->getState();
            
            if($state == 'add'){
                $c->change_field_type('user_update','hidden');
                $c->change_field_type('user_add', 'hidden', $this->dx_auth->get_user_id());  
            }elseif($state == 'edit'){
               $c->change_field_type('user_add','hidden');
               $c->change_field_type('user_update', 'hidden', $this->dx_auth->get_user_id());  
            }
            
            $c->set_field_upload('img_url', 'assets/uploads/images/events/');
            $c->callback_after_upload(array($this,'callback_event_after_upload'));
           
            $c->callback_before_insert(array($this, 'add_event_valid_url'));
            $c->callback_before_update(array($this, 'update_event_valid_url'));
            $c->callback_before_delete(array($this, 'delete_event_url'));
            //$c->callback_before_delete(array($this, 'delete_content'));
                 
    	    $output = $c->render();
           
            $this->_my_output($output);
        
        }else{
            
            redirect('auth/login');
        }

	}
    
    
    function slides()
	{
	    if ($this->dx_auth->is_role(array('admin','moderator')))    
        {
	    
            $c = new grocery_CRUD();
            
            $c->set_theme('datatables');  
            $c->set_table('slides');
            $c->columns('slide_id','title_cz','img_url','video');
            $c->fields('title_cz','img_url','link','video');
            $c->set_subject('слайд');
            $c->required_fields('title_cz','img_url','link');
            $c->order_by('slide_id', 'asc');
            
            $c->unset_read();
            
            $c->display_as('slide_id','№')
              ->display_as('title_ru','Заголовок RU')
              ->display_as('title_ua','Заголовок UA')
              ->display_as('title_en','Заголовок EN')
              ->display_as('title_cz','Заголовок CZ')
              ->display_as('img_url','Картинка')
              ->display_as('link','Ссылка напр. http://google.com/')
              ->display_as('video','Это видео?');
            
            $c->set_field_upload('img_url', 'assets/uploads/images/slides/');
            $c->callback_after_upload(array($this,'callback_slide_after_upload'));  
              
    	    $output = $c->render();
           
            $this->_my_output($output);
            
        }else{
            
            redirect('auth/login');
        }

	}
    
    function photos($material_id)
    {
        if ($this->dx_auth->is_role(array('admin','moderator')))    
        {
            
            $crud = new image_CRUD();
            
            $crud->set_table('photos');
            $crud->set_primary_key_field('photo_id');
    		$crud->set_url_field('img_url');
    		$crud->set_title_field('title');
            
    		$crud->set_relation_field('material')->set_ordering_field('priority')->set_image_path("assets/uploads/images/photos");
                 
    	    $output = $crud->render();
    			
    	    $this->_my_output($output);
           
       }else{
            
            redirect('auth/login');
       }
    }
    
    function comments()
	{
	    if ($this->dx_auth->is_role(array('admin','moderator')))    
        {
	    
            $c = new grocery_CRUD();
            
            $c->set_theme('flexigrid');
            $c->set_table('comments');
            
            $c->add_action('Ссылка на статью', base_url('assets/grocery_crud/themes/flexigrid/css/images/next.gif'), '' ,'', array($this,'get_comment_url'));
            
            $c->columns('author','email','comment_text','date');
            $c->set_subject('коммент');
            $c->fields('author','email','comment_text','date','active');
            $c->required_fields('author','email','comment_text','date');
            
            $c->display_as('author','Автор')
              ->display_as('email','Email')
              ->display_as('comment_text','Текст')
              //->display_as('rating','Рейтинг')
              ->display_as('date','Дата добавления')
              ->display_as('active','Активно?');
                 
    	   $output = $c->render();
           
           $this->_my_output($output);
       
       }else{
            
            redirect('auth/login');
       }

	}
    
    function set()
    {
        if ($this->dx_auth->is_role('admin'))  
        {
            
            $crud = new grocery_CRUD();
            
            $crud->unset_delete();
            $crud->unset_add();
            
            $crud->set_theme('datatables'); 
            $crud->set_table('settings');
            $crud->where('setting_id',1);
            $crud->columns('site_name_cz');
            $crud->set_subject('настройки');
            $crud->required_fields('site_name_ru','site_name_ua','site_name_en','site_name_cz','materials_per_page','latest_news_num','email_1');
            
            $crud->display_as('site_name_ru','Название сайта (RU)')
                 ->display_as('site_name_ua','Название сайта (UA)')
                 ->display_as('site_name_en','Название сайта (EN)')
                 ->display_as('site_name_cz','Название сайта (CZ)')
                 ->display_as('phone_1','Телефон 1')
                 ->display_as('phone_2','Телефон 2')
                 ->display_as('mob_phone_1','Моб. телефон 1')
                 ->display_as('mob_phone_2','Моб. телефон 2')
                 ->display_as('email_1','Email 1')
                 ->display_as('email_2','Email 2')
                 ->display_as('materials_per_page','Кол-во материалов на странице')
                 ->display_as('latest_news_num','Кол-во новостей в прокрутке')
                 ->display_as('vk','VK')
                 ->display_as('tw','Twitter')
                 ->display_as('facebook','Facebook')
                 ->display_as('google_plus','Google +')
                 ->display_as('address','Адресс');
                 
           $output = $crud->render();
           
           $this->_my_output($output);
       
       }else{
            
            redirect('auth/login');
       }
    }
    
    function callback_section_after_upload($uploader_response,$field_info, $files_to_upload)
    {
        $this->load->library('image_moo');
     
        //Is only one file uploaded so it ok to use it with $uploader_response[0].
        $file_uploaded = $field_info->upload_path.'/'.$uploader_response[0]->name; 
     
        $this->image_moo->
            load($file_uploaded)->
            save($file_uploaded,true);
        return true;
    }
    
    function callback_material_after_upload($uploader_response,$field_info, $files_to_upload)
    {
        $this->load->library('image_moo');
     
        $file_uploaded = $field_info->upload_path.'/'.$uploader_response[0]->name; 
        $file_thumbs_path = $field_info->upload_path.'/thumbs/'.$uploader_response[0]->name;
    
        $this->image_moo->
            load($file_uploaded)->
            resize(1024,768)->
            save($file_uploaded,true)->
            //resize_crop(320,240)->
            resize(320,240)->
            save($file_thumbs_path,true);
        return true;
    }
    
    function callback_event_after_upload($uploader_response,$field_info, $files_to_upload)
    {
        $this->load->library('image_moo');
     
        $file_uploaded = $field_info->upload_path.'/'.$uploader_response[0]->name; 
        $file_thumbs_path = $field_info->upload_path.'/thumbs/'.$uploader_response[0]->name;
    
        $this->image_moo->
            load($file_uploaded)->
            resize(1024,768)->
            save($file_uploaded,true)->
            //resize_crop(320,240)->
            resize(320,240)->
            save($file_thumbs_path,true);
        return true;
    }
    
    function callback_photo_after_upload($uploader_response,$field_info, $files_to_upload)
    {
        $this->load->library('image_moo');
     
        //Is only one file uploaded so it ok to use it with $uploader_response[0].
        $file_uploaded = $field_info->upload_path.'/'.$uploader_response[0]->name; 
        $file_thumbs_path = $field_info->upload_path.'/thumbs/'.$uploader_response[0]->name;
        
        $watermark="watermark.png";
     
        $this->image_moo->
            load($file_uploaded)->
            load_watermark('assets/uploads/images/'.$watermark)->
            resize(1024,768)->
            watermark(3)->
            save($file_uploaded,true)->
            resize_crop(120,80)->
            save($file_thumbs_path,true);
        return true;
    }
    
    function callback_friends_after_upload($uploader_response,$field_info, $files_to_upload)
    {
        $this->load->library('image_moo');
     
        //Is only one file uploaded so it ok to use it with $uploader_response[0].
        $file_uploaded = $field_info->upload_path.'/'.$uploader_response[0]->name; 
        $file_thumbs_path = $field_info->upload_path.'/thumbs/'.$uploader_response[0]->name;
        
        $watermark="watermark.png";
     
        $this->image_moo->
            load($file_uploaded)->
            load_watermark('assets/uploads/images/'.$watermark)->
            resize(1024,768)->
            watermark(3)->
            save($file_uploaded,true)->
            resize_crop(320,240)->
            save($file_thumbs_path,true);
        return true;   
    }
    
    function callback_newspapers_after_upload($uploader_response,$field_info, $files_to_upload)
    {
        $this->load->library('image_moo');
     
        //Is only one file uploaded so it ok to use it with $uploader_response[0].
        $file_uploaded = $field_info->upload_path.'/'.$uploader_response[0]->name; 
        $file_thumbs_path = $field_info->upload_path.'/thumbs/'.$uploader_response[0]->name;
        
        $watermark="watermark.png";
     
        $this->image_moo->
            load($file_uploaded)->
            load_watermark('assets/uploads/images/'.$watermark)->
            resize(1024,768)->
            //watermark(3)->
            save($file_uploaded,true)->
            resize_crop(320,240)->
            save($file_thumbs_path,true);
        return true;   
    }
    
    function callback_slide_after_upload($uploader_response,$field_info, $files_to_upload)
    {
        $this->load->library('image_moo');
     
        $file_uploaded = $field_info->upload_path.'/'.$uploader_response[0]->name; 
        $file_thumbs_path = $field_info->upload_path.'/thumbs/'.$uploader_response[0]->name;

        $this->image_moo->
            load($file_uploaded)->
            resize_crop(600,300)->
            save($file_uploaded,true)->
            resize_crop(90,60)->
            save($file_thumbs_path,true);
        
        return true;    
    }
    
    function callback_banner_after_upload($uploader_response,$field_info, $files_to_upload)
    {
        $this->load->library('image_moo');
     
        $file_uploaded = $field_info->upload_path.'/'.$uploader_response[0]->name; 
     
        $this->image_moo->
            load($file_uploaded)->
            resize(190,190)->
            save($file_uploaded,true);
        return true;    
    }
    
    function create_dir($post_array,$primary_key)
    {
        $folder = 'assets/uploads/images/photos/'.$primary_key.'/';
        $folder_thumb = 'assets/uploads/images/photos/'.$primary_key.'/thumbs/';
        
    	if(!is_dir($folder))
    	{
    		mkdir($folder, 0777);
    	
            if(!is_dir($folder_thumb))
    	    {
    	       mkdir($folder_thumb, 0777);
            }           	       
                    
        }  
    }
    
    function update_place($bilet_id)
    {
    
        $this->load->model('tickets_model');
        
        $this->tickets_model->reload_place($bilet_id);
        
        echo('Ok!');
        
        
    }
    
    function update_places()
    {
    
        $this->load->model('tickets_model');
        
        $this->tickets_model->reload_places();
        
        echo('Ok!');
        
        
    }
    
    function get_comment_url($primary_key , $row)
    {
        $this->load->model('materials_model');
        
        $data['material'] = $this->materials_model->get($row->foreign_id);
        
        //return base_url();
        return base_url().@$data['material']['url'];
        
        die;
    }
    
    function add_page_valid_url($post_array = array(), $primary_key = null)
    {
        
        $this->load->model('Routes_model');
        
        if(empty($post_array['url']) || $post_array['url']=='')
		{
            $post_array['url'] = url_title(convert_accented_characters($post_array['title']), 'dash', TRUE);
		}else{
		    $post_array['url'] = url_title(convert_accented_characters($post_array['url']), 'dash', TRUE);  
		}
        
        // validation URL
        $url			= $this->Routes_model->validate_url($post_array['url']);
	    $route['url']	= $url;	
        $route['route']	= 'pages/view/'.$url;	
	    $route_id		= $this->Routes_model->save($route);
        
        $post_array['url']      = $url;
	    $post_array['route_id'] = $route_id;
        
        return $post_array;
    }
    
    function update_page_valid_url($post_array = array(), $primary_key = null)
    {
		$this->load->model('Routes_model');
        
        if(empty($post_array['url']) || $post_array['url']=='')
		{
            $post_array['url'] = url_title(convert_accented_characters($post_array['title']), 'dash', TRUE);
		}else{
		    $post_array['url'] = url_title(convert_accented_characters($post_array['url']), 'dash', TRUE);  
		}
        
        // validation URL
        $url                = $this->Routes_model->validate_url($post_array['url'], $post_array['route_id']);
        $route['id']        = $post_array['route_id'];
        $route['url']	    = $url;
        $route['route']	    = 'pages/view/'.$url;	
	    $route_id           = $this->Routes_model->save($route);
        
        $post_array['url']      = $url;
        $post_array['route_id'] = $route_id;
        
        return $post_array;

    }
	
    function delete_page_url($primary_key)
    {
        $this->load->model('Routes_model');
        
        $this->db->where('page_id',$primary_key);
        $query = $this->db->get('pages')->row();
    			
        $this->Routes_model->delete($query->route_id);
    }
    
    function add_section_valid_url($post_array = array(), $primary_key = null)
    {
        
        $this->load->model('Routes_model');
        
        if($post_array['type'] != 'link'){
            
            if(empty($post_array['url']) || $post_array['url']=='')
		    {
                $post_array['url'] = url_title(convert_accented_characters($post_array['title']), 'dash', TRUE);
    		}else{
    		    $post_array['url'] = url_title(convert_accented_characters($post_array['url']), 'dash', TRUE);  
    		}
            
            // validation URL
            $url			= $this->Routes_model->validate_url($post_array['url']);
    	    $route['url']	= $url;	
            $route['route']	= 'sections/view/'.$url;	
    	    $route_id		= $this->Routes_model->save($route);
            
            $post_array['url']      = $url;
    	    $post_array['route_id'] = $route_id;
            
            return $post_array;
            
        }else{
            
            if(empty($post_array['url']) || $post_array['url']=='')
		    {
                $post_array['url'] = url_title(convert_accented_characters($post_array['title']), 'dash', TRUE);
    		}else{
    		    $post_array['url'] = url_title(convert_accented_characters($post_array['url']), 'dash', TRUE);  
    		}
            
            // validation URL
            $url			= $this->Routes_model->validate_url($post_array['url']);
    	    $route['url']	= $url;	
            $route['route']	= $url;	
    	    $route_id		= $this->Routes_model->save($route);
            
            $post_array['url']      = $url;
    	    $post_array['route_id'] = $route_id;
            
            return $post_array;
            
        }
        
        
    }
    
    function update_section_valid_url($post_array = array(), $primary_key = null)
    {
		$this->load->model('Routes_model');
        
        if($post_array['type'] != 'link'){
        
            if(empty($post_array['url']) || $post_array['url']=='')
    		{
                $post_array['url'] = url_title(convert_accented_characters($post_array['title']), 'dash', TRUE);
    		}else{
    		    $post_array['url'] = url_title(convert_accented_characters($post_array['url']), 'dash', TRUE);  
    		}
            
            // validation URL
            $url                = $this->Routes_model->validate_url($post_array['url'], $post_array['route_id']);
            $route['id']        = $post_array['route_id'];
            $route['url']	    = $url;
            $route['route']	    = 'sections/view/'.$url;	
    	    $route_id           = $this->Routes_model->save($route);
            
            $post_array['url']      = $url;
            $post_array['route_id'] = $route_id;
            
            return $post_array;
            
         }else{
            
            if(empty($post_array['url']) || $post_array['url']=='')
    		{
                $post_array['url'] = url_title(convert_accented_characters($post_array['title']), 'dash', TRUE);
    		}else{
    		    $post_array['url'] = url_title(convert_accented_characters($post_array['url']), 'dash', TRUE);  
    		}
            
            // validation URL
            $url                = $this->Routes_model->validate_url($post_array['url'], $post_array['route_id']);
            $route['id']        = $post_array['route_id'];
            $route['url']	    = $url;
            $route['route']	    = $url;	
    	    $route_id           = $this->Routes_model->save($route);
            
            $post_array['url']      = $url;
            $post_array['route_id'] = $route_id;
            
            return $post_array;
            
         }

    }
    
    function delete_section_url($primary_key)
    {
        $this->load->model('Routes_model');
        
        $this->db->where('section_id',$primary_key);
        $query = $this->db->get('sections')->row();
    			
        $this->Routes_model->delete($query->route_id);
    }
    
    function add_material_valid_url($post_array = array(), $primary_key = null)
    {
        
        $this->load->model('Routes_model');
        
        if(empty($post_array['url']) || $post_array['url']=='')
		{
            $post_array['url'] = url_title(convert_accented_characters($post_array['title']), 'dash', TRUE);
		}else{
		    $post_array['url'] = url_title(convert_accented_characters($post_array['url']), 'dash', TRUE);  
		}
        
        // validation URL
        $url			= $this->Routes_model->validate_url($post_array['url']);
	    $route['url']	= $url;	
        $route['route']	= 'materials/view/'.$url;	
	    $route_id		= $this->Routes_model->save($route);
        
        $post_array['url']      = $url;
	    $post_array['route_id'] = $route_id;
        
        return $post_array;
    }
    
    function update_material_valid_url($post_array = array(), $primary_key = null)
    {
		$this->load->model('Routes_model');
        
        //die($post_array['route_id']);
        
        //if it's empty assign the name field
		if(empty($post_array['url']) || $post_array['url']=='')
		{
			$post_array['url'] = $post_array['title_ru'];
            $post_array['url'] = url_title(convert_accented_characters($post_array['url']), 'dash', TRUE);
		}
        
        // validation URL
        $url                = $this->Routes_model->validate_url($post_array['url'], $post_array['route_id']);
        $route['id']        = $post_array['route_id'];
        $route['url']	    = $url;
        $route['route']	    = 'materials/view/'.$url;	
	    $route_id           = $this->Routes_model->save($route);
        
        $post_array['url']      = $url;
        $post_array['route_id'] = $route_id;
        
        return $post_array;

    }
	
    function delete_material_url($primary_key)
    {
        $this->load->model('Routes_model');
        
        $this->db->where('material_id',$primary_key);
        $query = $this->db->get('materials')->row();
    			
        $this->Routes_model->delete($query->route_id);
    }
    
    function delete_content($primary_key)
    {
        $this->load->model('Materials_model');
        
        $this->db->select('content_id');
        $this->db->where('fid',$primary_key);
        $query = $this->db->get('content')->result_array();
    	
        foreach($query as $content){
            $this->Materials_model->delete_content($content['content_id']);
        }
        		
    }
    
    function add_event_valid_url($post_array = array(), $primary_key = null)
    {
        
        $this->load->model('Routes_model');
        
        if(empty($post_array['url']) || $post_array['url']=='')
		{
            $post_array['url'] = url_title(convert_accented_characters($post_array['title']), 'dash', TRUE);
		}else{
		    $post_array['url'] = url_title(convert_accented_characters($post_array['url']), 'dash', TRUE);  
		}
        
        // validation URL
        $url			= $this->Routes_model->validate_url($post_array['url']);
	    $route['url']	= $url;	
        $route['route']	= 'events/view/'.$url;	
	    $route_id		= $this->Routes_model->save($route);
        
        $post_array['url']      = $url;
	    $post_array['route_id'] = $route_id;
        
        return $post_array;
    }
    
    function update_event_valid_url($post_array = array(), $primary_key = null)
    {
		$this->load->model('Routes_model');
        
        //die($post_array['route_id']);
        
        //if it's empty assign the name field
		if(empty($post_array['url']) || $post_array['url']=='')
		{
			$post_array['url'] = $post_array['title_ru'];
            $post_array['url'] = url_title(convert_accented_characters($post_array['url']), 'dash', TRUE);
		}
        
        // validation URL
        $url                = $this->Routes_model->validate_url($post_array['url'], $post_array['route_id']);
        $route['id']        = $post_array['route_id'];
        $route['url']	    = $url;
        $route['route']	    = 'events/view/'.$url;	
	    $route_id           = $this->Routes_model->save($route);
        
        $post_array['url']      = $url;
        $post_array['route_id'] = $route_id;
        
        return $post_array;

    }
	
    function delete_event_url($primary_key)
    {
        $this->load->model('Routes_model');
        
        $this->db->where('event_id',$primary_key);
        $query = $this->db->get('events')->row();
    			
        $this->Routes_model->delete($query->route_id);
    }
    
    public function reorder($pcat)
    {
        if ($this->dx_auth->is_role('admin'))  
        {
            $this->load->model('sections_model');
            
            $data['back'] = anchor("adminpanel/sections/$pcat", "К списку рубрик",'class="back"');
            $data['cats_list'] = $this->sections_model->get_subs($pcat);
            $data['cats_reorder'] = TRUE;
            
            $name = 'reorder_cats';
            $this->display_lib->admin_page($data,$name);
        
        }else{
            
            redirect('auth/login');
        }
    }

    public function save_order()
    {
        $this->load->model('sections_model');
        
        $items = $this->input->post('item');
        
        $this->sections_model->sections_reorder($items);
        
        $this->session->set_flashdata('result', 'Рубрики успешно отсортированы!');
    
        redirect('adminpanel/', 'refresh');
    
    }
    
    function _callback_date($value, $row)
    {
        return "<span style='visibility:hidden;display:none;'>".date('Y-m-d H:i:s', strtotime($value))."</span>".date('d/m/Y H:i:s', strtotime($value));
    }

    
}