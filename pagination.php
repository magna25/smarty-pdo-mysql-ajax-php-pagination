<?php 
/*
   Smarty - pdo/mysqli pagination: 12/28/14
*/
class Pagination{
	public function __construct($options){
		//@defaults
		$this->current = 1;
		$this->limit = 10;
		$this->adjacent = 2;
		$this->pClass = 'pagination';
		$this->nextL = '&gt;';
		$this->prevL = '&lt;';
		$this->firstL = '&laquo;';
		$this->lastL = '&raquo;';
		$this->options = (object) $options;
		$this->counter = true;
		$this->showFirstLast = true;
		$this->ellipsis = true;
		$this->ajax = false;
	}
	public function limit($val){$this->limit = $val;}
	public function adjacent($val){$this->adjacent = $val;}
	public function target($val){$this->target = $val;}
	public function current($val){$this->current = $val;}
	public function nextL($val){$this->nextL = $val;}
	public function prevL($val){$this->prevL = $val;}
	public function firstL($val){$this->firstL = $val;}
	public function lastL($val){$this->lastL = $val;}
	public function pClass($val){$this->pClass = $val;}
	public function counter($val){$this->counter = $val;}
	public function ellipsis($val){$this->ellipsis = $val;}
	public function ajax($val){$this->ajax = $val;}
	public function showFirstLast($val){$this->showFirstLast = $val;}
	
	private function refValues($arr){ 
		$refs = array(); 
		foreach($arr as $key => $value) 
			$refs[$key] = &$arr[$key]; 
		return $refs; 
	} 
	
	//get the total pages
	private function total(){
		if($this->options->db_type == 'pdo'){
			preg_match('~select(.*?)from~', strtolower($this->options->sql), $output);
			$sql = str_replace(trim($output[1]), "count(1)",$this->options->sql);
			$this->options->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$stmt = $this->options->db->prepare($sql);
			$stmt->execute($this->options->params);
			return $stmt->fetchColumn(0);
		}
		else if($this->options->db_type == 'mysql'){
			$stmt = $this->options->db->prepare($this->options->sql);
			echo $this->options->db->error;
			if($this->options->params){
				call_user_func_array(array($stmt, "bind_param"), array_merge(array(str_repeat('s', count($this->options->params))), $this->refValues($this->options->params)));
			}
			$stmt->execute();
			$res = $stmt->get_result();
			return $res->num_rows;
		}
		return false;
	}
	
  //result from the query
	public function result($limit){
		if($this->current > $this->pages()){
			$limit = " LIMIT 0,".$this->limit;
		}
		if($this->options->db_type == 'pdo'){
			$this->options->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$stmt = $this->options->db->prepare($this->options->sql.$limit);
			$stmt->execute($this->options->params);
			$res = $stmt->fetchAll();
			return $res;
		}
		elseif($this->options->db_type == 'mysql'){
			$stmt = $this->options->db->prepare($this->options->sql.$limit);
			if($this->options->params){
				call_user_func_array(array($stmt, "bind_param"), array_merge(array(str_repeat('s', count($this->options->params))), $this->refValues($this->options->params)));
			}
			$stmt->execute();
			$res = $stmt->get_result();
			return $res;
		}
		return false;
	}
	private function pages(){
		return ceil($this->total()/$this->limit);
	}
	
	//next and last links
	private function nextLast(){
		if($this->current >= $this->pages()){
			if($this->showFirstLast){
				$next = "<li class='disabled'><a>".$this->nextL."</a></li> <li class='disabled'><a>".$this->lastL."</a></li>";
			}
			else{
				$next = "<li class='disabled'><a>".$this->nextL."</a></li>";
			}
		}
		else{
			if($this->showFirstLast){
				$next =  $this->ajax ? "<li data-page='".($this->current + 1)."'><a href='#".($this->current + 1)."'>".$this->nextL."</a></li> <li data-page='".$this->pages()."'><a href='#".($this->pages())."'>".$this->lastL."</a></li>" : "<li><a href='".$this->target."?pg=".($this->current + 1)."'>".$this->nextL."</a></li> <li><a href='".$this->target."?pg=".($this->pages())."'>".$this->lastL."</a></li>";
			}
			else{
				$next = $this->ajax ? "<li data-page='".($this->current + 1)."'><a href='#".($this->current + 1)."'>".$this->nextL."</a></li>" :"<li><a href='".$this->target."?pg=".($this->current + 1)."'>".$this->nextL."</a></li>";
			}
		}
		return $next;
	}
	
	//prev and first links
	private function prevFirst(){
		if($this->current <= 1){
			if($this->showFirstLast){
				$prev = "<li class='disabled'><a>".$this->firstL."</a></li> <li class='disabled'><a>".$this->prevL."</a></li>";
			}
			else{
				$prev = "<li class='disabled'><a>".$this->prevL."</a></li>";
			}
		}
		else{
			if($this->showFirstLast){
				$prev = $this->ajax ? "<li data-page='1'><a href='#1'>".$this->firstL."</a></li> <li data-page='".($this->current - 1)."'><a  href='#".($this->current - 1)."'>".$this->prevL."</a></li>" : "<li><a href='".$this->target."?pg=1'>".$this->firstL."</a></li> <li><a href='".$this->target."?pg=".($this->current - 1)."'>".$this->prevL."</a></li>";
			}
			else{
				$prev = $this->ajax ? "<li data-page='".($this->current - 1)."'><a href='#".($this->current - 1)."'>".$this->prevL."</a></li>" : "<li><a href='".$this->target."?pg=".($this->current - 1)."'>".$this->prevL."</a></li>";
			}
		}
		return $prev;
	}
	
	//build and return links
	public function showLinks(){
		$maxLinks = ($this->adjacent*2)+1;
		$maxLinks = ($this->pages() < $maxLinks)?($this->pages()): $maxLinks;
		if($this->current >= $maxLinks){
			$start = ($this->current <= $this->adjacent)? 1:($this->current - $this->adjacent);
			$end = ($this->pages() - $this->current >= $this->adjacent)?($this->current+$this->adjacent): $this->pages();
		}
		else{
			$start = 1;
			$end = $maxLinks;
		}
		$links = '<ul class="'.$this->pClass.'">';
		$links .= $this->prevFirst();
		if($this->counter){
			if($this->ellipsis && $this->current >= $maxLinks && $this->current-$this->adjacent != 1){
						$links .= '<li><a>...</a></li>';
					}
				for($counter = $start; $counter <= $end; $counter++){
					if($counter == $this->current){
						$links .= "<li class='active'><a>".$counter."</a></li>";
					}
					else{
						if($this->ajax){
							$links .= "<li data-page='".$counter."'><a href='#".$counter."'>".$counter."</a></li>";
						}
						else{
							$links .= "<li><a href='".$this->target."?pg=".$counter."'>".$counter."</a></li>";
						}
					}
				}
				if($this->ellipsis && $this->pages() >= $maxLinks && $this->current+$this->adjacent < $this->pages()){
						$links .= '<li><a>...</a></li>';
				}
		}
		$links .= $this->nextLast();
		$links .= "</ul><br/>";
		if($this->limit < $this->total()){
			if($this->current <= $this->pages()){
				return $links;
			}
				$this->current = 1;
				return $this->showLinks();
		}
	}
}
```
