<?php
/*
 # -- BEGIN LICENSE BLOCK ----------------------------------
 #
 # This file is part of MAGIX CMS.
 # MAGIX CMS, The content management system optimized for users
 # Copyright (C) 2008 - 2019 magix-cms.com <support@magix-cms.com>
 #
 # OFFICIAL TEAM :
 #
 #   * Gerits Aurelien (Author - Developer) <aurelien@magix-cms.com> <contact@aurelien-gerits.be>
 #
 # Redistributions of files must retain the above copyright notice.
 # This program is free software: you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation, either version 3 of the License, or
 # (at your option) any later version.
 #
 # This program is distributed in the hope that it will be useful,
 # but WITHOUT ANY WARRANTY; without even the implied warranty of
 # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 # GNU General Public License for more details.

 # You should have received a copy of the GNU General Public License
 # along with this program.  If not, see <http://www.gnu.org/licenses/>.
 #
 # -- END LICENSE BLOCK -----------------------------------

 # DISCLAIMER

 # Do not edit or add to this file if you wish to upgrade MAGIX CMS to newer
 # versions in the future. If you wish to customize MAGIX CMS for your
 # needs please refer to http://www.magix-cms.com for more information.
 */
//include_once ('db.php');
class plugins_attachfile_core extends plugins_attachfile_db
{

    protected $template, $modelPlugins, $message, $arrayTools, $data,
        $modelLanguage, $collectionLanguage, $progress;
    public $controller, $plugins, $plugin, $edit, $id_paf, $file, $subaction,$order, $offset;
    /**
     * @var array|string[]
     */
    public array $allowedExts = [
        "pdf","xls","xlsx","docx"
    ];

    public function __construct($t = null)
    {
        $this->template = $t ? $t : new backend_model_template();
        $this->modelPlugins = new backend_model_plugins();
        $this->plugins = new backend_controller_plugins();
        $formClean = new form_inputEscape();
        $this->message = new component_core_message($this->template);
        $this->arrayTools = new collections_ArrayTools();
        $this->data = new backend_model_data($this);
        $this->modelLanguage = new backend_model_language($this->template);
        $this->collectionLanguage = new component_collections_language();
        if (http_request::isGet('controller')) {
            $this->controller = $formClean->simpleClean($_GET['controller']);
        }
        if (http_request::isGet('plugin')) {
            $this->plugin = $formClean->simpleClean($_GET['plugin']);
        }
        // --- ADD or EDIT
        if (http_request::isGet('edit')) $this->edit = $formClean->numeric($_GET['edit']);
        if (http_request::isGet('id')) $this->id_paf = $formClean->simpleClean($_GET['id']);
        elseif (http_request::isPost('id')) $this->id_paf = $formClean->simpleClean($_POST['id']);
        if (http_request::isPost('product')) $this->order = $formClean->arrayClean($_POST['product']);
        if (http_request::isGet('offset')) $this->offset = intval(form_inputEscape::simpleClean($_GET['offset']));
        if (isset($_FILES['file']["name"])) $this->file = $_FILES['file']["name"];
        if (http_request::isGet('mod')) $this->subaction = form_inputEscape::simpleClean($_GET['mod']);

    }
    /**
     * Method to override the name of the plugin in the admin menu
     * @return string
     */
    public function getExtensionName()
    {
        return $this->template->getConfigVars('attachfile_product_plugin');
    }
    /**
     * Assign data to the defined variable or return the data
     * @param string $type
     * @param string|int|null $id
     * @param string $context
     * @param boolean $assign
     * @param boolean $pagination
     * @return mixed
     */
    private function getItems($type, $id = null, $context = null, $assign = true, $pagination = false)
    {
        return $this->data->getItems($type, $id, $context, $assign, $pagination);
    }
    /**
     * @return string[]
     */
    public function getTabsAvailable() : array{
        return ['product'];
    }
    /**
     * Update data
     * @param $data
     * @throws Exception
     */
    private function add($data)
    {
        switch ($data['type']) {
            case 'productAttach':
                parent::insert(
                    array(
                        'context' => $data['context'],
                        'type' => $data['type']
                    ),
                    $data['data']
                );
                break;
        }
    }
    /**
     * Update data
     * @param $data
     * @throws Exception
     */
    private function upd($data)
    {
        switch ($data['type']) {
            case 'order':
                $p = $this->order;
                for ($i = 0; $i < count($p); $i++) {
                    parent::update(
                        ['type'=>$data['type']],
                        [
                            'id_pdn' => $p[$i],
                            'order_pdn' => $i + (isset($this->offset) ? ($this->offset + 1) : 0)
                        ]
                    );
                }
                break;
            case 'productAttach':
                parent::update(
                    array(
                        'context' => $data['context'],
                        'type' => $data['type']
                    ),
                    $data['data']
                );
                break;
        }
    }
    /**
     * Insertion de données
     * @param $data
     * @throws Exception
     */
    private function del($data)
    {
        switch($data['type']){
            case 'delAttach':
                parent::delete(
                    array(
                        'type' => $data['type']
                    ),
                    $data['data']
                );
                $this->message->json_post_response(true,'delete',$data['data']);
                break;
        }
    }

    /**
     * @return void
     * @throws DailymotionApiException
     * @throws DailymotionAuthRequiredException
     */
    private function setUploadAttach(){
        if(isset($this->file)) {

            $log = new debug_logger(MP_LOG_DIR);
            //$log->tracelog('upload start');
            $this->template->configLoad();
            $this->progress = new component_core_feedback($this->template);
            $extension = pathinfo($_FILES['file']["name"], PATHINFO_EXTENSION);
            usleep(200000);
            $this->progress->sendFeedback(array('message' => $this->template->getConfigVars('control_of_data'), 'progress' => 30));
            //$log->tracelog(json_encode($_FILES));
            $nbAttachProduct = $this->getItems('nbAttachProduct', array('id' => $this->edit), 'one', false);
            $prefixName = ($nbAttachProduct['nbfile'] + 1).'_';//$nbVideoProduct['nbvideo'] > 0 ? $nbVideoProduct['nbvideo'].'_' : '';
            $defaultLanguage = $this->collectionLanguage->fetchData(array('context' => 'one', 'type' => 'default'));

            $productData = $this->getItems('productData', array('id' => $this->edit,'default_lang'=>$defaultLanguage['id_lang']), 'one', false);
            $fileName = !empty($productData['reference_p']) ? $productData['reference_p'] : $productData['name_p'];
            $fileUpload = new component_files_upload();
            $cleanfilename = http_url::clean($prefixName.$fileName);
            $resultUpload = $fileUpload->setUploadFile(
                'file',
                ['name'=>$cleanfilename],
                [
                    'upload_root_dir' => 'upload/attachfile/'.$this->edit, //string
                    //'upload_dir' => $this->edit //string ou array
                ],
                $this->allowedExts,
                false
            );
            if($resultUpload){
                //$log->tracelog(json_encode($resultUpload));
                // Add video data
                $this->add([
                    'type' => 'productAttach',
                    'data' => [
                        'id_product'=>$this->edit,
                        'name_paf'  =>$cleanfilename,
                        'type_paf'  =>$resultUpload['type']
                    ]
                ]);

                $videoUrl = $resultUpload['path'].$resultUpload['file'];
                //$log->tracelog($videoUrl);
                $percent = $this->progress->progress;
                $preparePercent = (80 - $percent) / count($resultUpload);
                $percent = $percent + $preparePercent;
                usleep(200000);
                $this->progress->sendFeedback(['message' => $this->template->getConfigVars('upload_on_attachfile'), 'progress' => $percent]);

                //$log->tracelog(json_encode($_FILES));
                //$log->tracelog(json_encode($resultUpload));
                $lastVideo = $this->getItems('lastAttach', NULL, 'one', false);
                if(!empty($lastVideo)){
                    usleep(200000);
                    $this->getAttachList();
                    $display = $this->modelPlugins->fetch('mod/file.tpl');
                    $this->progress->sendFeedback(array('message' => $this->template->getConfigVars('file_success'), 'progress' => 100, 'status' => 'success', 'result' => $display));

                }else{
                    usleep(200000);
                    $this->progress->sendFeedback(array('message' => $this->template->getConfigVars('error_format'), 'progress' => 100, 'status' => 'error', 'error_code' => 'error_data'));
                }

            }else {
                //$log->tracelog(json_encode($_FILES));
                usleep(200000);
                $this->progress->sendFeedback(array('message' => $this->template->getConfigVars('error_format'), 'progress' => 100, 'status' => 'error', 'error_code' => 'error_data'));

            }
        }
    }

    /**
     * @return void
     */
    protected function getAttachList()
    {
        $video = $this->getItems('attach',['id' => $this->edit],'all', false);

        $newVideo = [];
        foreach($video as $key => $item){
            $newVideo[$key]['id_paf'] = $item['id_paf'];
            $newVideo[$key]['name_paf'] = $item['name_paf'];
            $newVideo[$key]['type_paf'] = $item['type_paf'];
        }
        $this->template->assign('files',$newVideo);
        $assign = [
            'id_paf',
            'name_paf' => ['title' => 'name'],
            'type_paf' => ['title' => 'name']
        ];
        $this->data->getScheme(['mc_product_attachfile'], ['id_paf','name_paf','type_paf'], $assign);
    }

    /**
     * Execution du plugin dans un ou plusieurs modules core
     */
    public function run(){
        if(isset($this->controller)){
            switch ($this->controller) {
                case 'about':
                    $extends = $this->controller.(!isset($this->action) ? '/index.tpl' : '/pages/edit.tpl');
                    break;
                case 'category':
                case 'product':
                    $extends = 'catalog/'.$this->controller.'/edit.tpl';
                    break;
                case 'news':
                case 'catalog':
                    $extends = $this->controller.'/index.tpl';
                    break;
                case 'pages':
                    $extends = $this->controller.'/edit.tpl';
                    break;
                default:
                    $extends = 'index.tpl';
            }
            $this->template->assign('extends',$extends);
            if (isset($this->subaction)) {

                switch($this->subaction){
                    case 'add':
                        if(isset($_FILES['file']["name"])){
                            $this->setUploadAttach();
                        }
                        break;
                    case 'delete':
                        if(isset($this->id_paf)){
                            $makefile = new filesystem_makefile();
                            $fileData = $this->getItems('attachId', $this->id_paf, 'one', false);
                            $fileUrl = component_core_system::basePath().'upload/attachfile/'.$this->edit.'/'.$fileData['name_paf'].'.'.$fileData['type_paf'];
                            if(file_exists($fileUrl)) {
                                $makefile->remove($fileUrl);
                            }
                             $this->del([
                                'type' => 'delAttach',
                                'data' => [
                                    'id'    =>  $this->id_paf
                                ]
                            ]);
                        }
                        break;
                    case 'order':
                        if (isset($this->order) && is_array($this->order)) {
                            $this->upd([
                                'type' => 'order'
                            ]);
                        }
                        break;
                }
            }else {
                if ($this->controller == 'product') {
                    $defaultLanguage = $this->collectionLanguage->fetchData(array('context' => 'one', 'type' => 'default'));
                    $this->getAttachList();
                    $this->modelPlugins->display('mod/index.tpl');
                }
            }
        }
    }
}