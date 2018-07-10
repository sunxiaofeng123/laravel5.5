<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Auth;
use Mail;

class UsersController extends Controller
{
    public function __construct()
    {
        //除了该ji个方法，其余只用登陆的用户才能访问
        $this->middleware('auth',[
            'except' => ['show', 'create', 'store', 'index', 'confirmEmail']
        ]);

        //只有为登陆的用户才能访问
        $this->middleware('guest', ['only' =>['create']]);
    }

    //用户列表
    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }
    //
    public function create()
    {
        return view('users.create');
    }

    public function show(User $user)
    {
        $statuses = $user->statuses()
                         ->orderBy('created_at', 'desc')
                         ->paginate(30);

        return view('users.show', compact('user', 'statuses'));
    }

    //sign-up
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'     => 'required|max:50',
            'email'    => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password)
        ]);

        //注册成功邮件验证邮箱
        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的邮箱上， 请注意查收。');

        return redirect('/');
    }

    //邮件发送
    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'aufree@yousails.com';
        $name = 'Aufree';
        $to   = $user->email;
        $subject = '感谢注册 SAMPLE 应用！请确认你的邮箱。';

        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });

    }

    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show',[$user]);
    }

    //
    public function edit(User $user)
    {
        //判断是否为登陆的用户和访问该页面的用户是否相同的策略
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    //修改用户信息
    public function update(User $user, Request $request)
    {
        $this->validate($request,[
            'name'      => 'required|max:50',
            'password'  => 'nullable|confirmed|min:6'
        ]);

        //判断是否为登陆的用户和访问该页面的用户是否相同的策略
        $this->authorize('update', $user);

        $data = [];
        $data['name'] = $request->name;

        if ($request->password) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        session()->flash('success','个人资料修改成功');
        return redirect()->route('users.show', $user->id);
    }

    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户');
        return back();
    }

}
