<?php

namespace Phalcon\Mvc\Controller\Traits;


trait RedirectsRequests
{

	protected $redirectMessages = [];

	protected function getControllerRoute()
	{
		$basePath = explode(".", router()->getMatchedRoute()->getName());
		array_pop($basePath);
		return implode(".", $basePath);
	}

	protected function redirectNotFound($url = null)
	{
		if (!is_null($url)){
			return redirect($url, "error", ucfirst($this->getRedirectMessage("not_found")));
		}
		return redirect_back("error", ucfirst($this->getRedirectMessage("not_found")));
	}

	//Redirect on saving

	protected function redirectSave($id = null, $messages = null, $url = null)
	{
		$redirect = is_null($url)? route($this->getControllerRoute() . ".index") : $url;

		$action = request()->get("_action");
		if (!is_null($id) && $action == "edit") {
			$redirect .= "/$id/edit";
		}

		if ($action == "create") {
			$redirect .= "/create";
		}


		//$redirect = redirect($redirect)->with("token", \Tymon\JWTAuth\Facades\JWTAuth::getToken());
		return redirect($redirect, $messages);
	}

	protected function redirectSavedSuccess($id = null, $url = null)
	{
		return $this->redirectSave($id, ["success" => ucfirst($this->getRedirectMessage("save_success"))], $url);
	}

	protected function redirectSaveFailed($id = null, $url = null)
	{
		return $this->redirectSave($id, ["error" => ucfirst($this->getRedirectMessage("save_failed"))], $url);
	}

	//Redirect on deleting

	protected function redirectDelete($messages = null, $url = null)
	{
		$redirect = is_null($url)? $this->getControllerRoute() : $url;
		return redirect($redirect, $messages);
	}

	protected function redirectDeletedSuccess($url = null)
	{
		return $this->redirectDelete(["success" => ucfirst($this->getRedirectMessage("delete_success"))], $url);
	}

	protected function redirectDeleteFailed($url = null)
	{
		return $this->redirectDelete(["error" => ucfirst($this->getRedirectMessage("delete_failed"))], $url);
	}

	//Redirect on Not found

	protected function redirectUnauthorized($action = null, $basePath = null)
	{
		//$redirect = is_null($basePath)? $this->getControllerName() : $basePath;

		$message = ucfirst($this->getRedirectMessage("unauthorized"));

		if (!is_null($action)){
			$permission = Permissions::findFirst(["title = $action"]);
			if (!empty($permission)){
				$message .= "<div><small><strong>عنوان مجوز:</strong> %s </small></div>";
				$message = sprintf($message, $permission->label);
			}
		}

		if ($basePath !== null) {
			return redirect($basePath,"error", $message);
		}

		if (redirect()->back()->getRequest()->getRequestUri() !== request()->getRequestUri()) {
			return redirect_back("error", $message);
		}

	}

	protected function redirectOperationFailed($url = null)
	{
		if (!is_null($url)){
			return redirect($url, "error", ucfirst($this->getRedirectMessage("operation_failed")));
		}
		return redirect_back("error", ucfirst($this->getRedirectMessage("operation_failed")));
	}

	protected function getRedirectMessage($key = null)
	{
		return "";
	}

}