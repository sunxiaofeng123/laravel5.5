<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Auth;

class UsersController extends Controller
{
    public function __construct()
    {
        //除了该ji个方法，其余只用登陆的用户才能访问
        $this->middleware('auth',[
            'except' => ['show', 'create', 'store', 'index']
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
        return view('users.show', compact('user'));
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

        //注册成功自动登陆
        Auth::login($user);
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');

        return redirect()->route('users.show', [$user]);
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
