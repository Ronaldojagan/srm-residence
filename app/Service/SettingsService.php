<?php

namespace App\Service;

use App\Enums\UserType;
use App\Mail\UserDeleteMail;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SettingsService
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function profile(Request $request)
    {
        $data = User::where('id', auth()->user()->id)->where('status', true)->first();
        return view('backend.my_profile', compact('data'));
    }

    public function profileEdit(Request $request)
    {
        $data = User::where('id', auth()->user()->id)->where('status', true)->first();
        return view('backend.profile_edit', compact('data'));
    }

    public function updateProfile(array $data)
    {
        DB::beginTransaction();
        $user = User::findOrFail(auth()->id());
        $user->fill($data);
        $user->save();
        DB::commit();
        session()->flash("success", "Profile has been successfully updated!");
        return redirect('/admin/profile');
    }

    public function password(Request $request)
    {
        $data = User::where('id', auth()->user()->id)->where('status', true)->first();
        return view('backend.my_password', compact('data'));
    }

    public function updatePassword(array $data, User $user)
    {
        $currentPassword = auth()->user()->password;
        $newpassword = $data['new_password'];
        $oldpassword = $data['old_password'];
        $confirmpassword = $data['confirm_password'];
        if ($data['new_password'] != null) {
            if ($oldpassword !== null ||  $newpassword !== null || $confirmpassword !== null) {
                if (Hash::check($oldpassword, $currentPassword)) {
                    if ($newpassword == $confirmpassword) {
                        $data['password'] = Hash::make($data['new_password']);
                        $user = User::where('id', auth()->user()->id)->where('status', true)->first();
                        $user->fill($data);
                        $user->save();
                    } else {
                        session()->flash("error", "New Password and Confirm Password should be same!");
                        if ($user->type == UserType::SUPERADMIN) {
                            return redirect('/admin/profile');
                        } elseif ($user->type == UserType::SHOP) {
                            return redirect('/shop-admin/profile');
                        }
                    }
                } else {
                    session()->flash("error", "Old Password do not Match!");
                    if ($user->type == UserType::SUPERADMIN) {
                        return redirect('/admin/profile');
                    } elseif ($user->type == UserType::SHOP) {
                        return redirect('/shop-admin/profile');
                    }
                }
            }
        }
        session()->flash("success", "Password has been Successfully Updated!");
        if ($user->type == UserType::SUPERADMIN) {
            return redirect('/admin/profile');
        } elseif ($user->type == UserType::SHOP) {
            return redirect('/shop-admin/profile');
        }
    }

    public function settingsIndex()
    {
        $settings = [];
        $terms = Application::where('group', 'Terms and Conditions')->get();
        foreach ($terms as $key => $value) {
            if ($value['value'] != null) {
                $settings[$value['name']] = $value['value'];
            }
        }

        $refund = Application::where('group', 'Refund Policy')->get();
        foreach ($refund as $key => $value) {
            if ($value['value'] != null) {
                $settings[$value['name']] = $value['value'];
            }
        }

        $contact = Application::where('group', 'Contact')->get();
        foreach ($contact as $key => $value) {
            if ($value['value'] != null) {
                $settings[$value['name']] = $value['value'];
            }
        }

        $customer = Application::where('group', 'Customer')->get();
        foreach ($customer as $key => $value) {
            if ($value['value'] != null) {
                $settings[$value['name']] = $value['value'];
            }
        }

        $commission = Application::where('group','Rush Order Commission')->get();
        foreach ($commission as $key => $value) {
            if( $value['value']!=null ){
                $settings[$value['name']]=$value['value'];
            }
        }

        $charges = Application::where('group','Shop Radius')->get();
        foreach ($charges as $key => $value) {
            if( $value['value']!=null ){
                $settings[$value['name']]=$value['value'];
            }
        }

        $deliveryCharge = Application::where('group','Delivery Charge')->get();
        foreach ($deliveryCharge as $key => $value) {
            if( $value['value']!=null ){
                $settings[$value['name']]=$value['value'];
            }
        }

        $returnReplaceDays = Application::where('group','Return Replace Days')->get();
        foreach ($returnReplaceDays as $key => $value) {
            if( $value['value']!=null ){
                $settings[$value['name']]=$value['value'];
            }
        }

        $freeDeliveryAmountAbove = Application::where('group','Free Delivery Amount Above')->get();
        foreach ($freeDeliveryAmountAbove as $key => $value) {
            if( $value['value']!=null ){
                $settings[$value['name']]=$value['value'];
            }
        }

        $platformFee = Application::where('group','Platform Fee')->get();
        foreach ($platformFee as $key => $value) {
            if( $value['value']!=null ){
                $settings[$value['name']]=$value['value'];
            }
        }

        return $settings;
    }

    public function termsConditions(array $data)
    {
        $terms = $data;
        foreach ($terms as $key => $value) {
            if ($key != '_token' && $key != 'name') {
                if (Application::where("name", $key)->count() == 0) {
                    Application::create(['name' => $key, "value" => $value, "group" => "Terms and Conditions"]);
                } else {
                    Application::where("name", $key)->update(["value" => $value, "group" => "Terms and Conditions"]);
                }
            }
        }
        session()->flash("success", "Terms and Conditions has been Successfully Updated!");
        return redirect('/admin/settings');
    }

    public function refundPolicy(array $data)
    {
        foreach ($data as $key => $value) {
            if ($key != '_token' && $key != 'name') {
                if (Application::where("name", $key)->count() == 0) {
                    Application::create(['name' => $key, "value" => $value, "group" => "Refund Policy"]);
                } else {
                    Application::where("name", $key)->update(["value" => $value, "group" => "Refund Policy"]);
                }
            }
        }
        session()->flash("success", "Return & Refund Policy has been Successfully Updated!");
        return redirect('/admin/settings');
    }

    public function contact(array $data)
    {
        $contact = $data;
        foreach ($contact as $key => $value) {
            if ($key != '_token' && $key != 'name') {
                if (Application::where("name", $key)->count() == 0) {
                    Application::create(['name' => $key, "value" => $value, "group" => "Contact"]);
                } else {
                    Application::where("name", $key)->update(["value" => $value, "group" => "Contact"]);
                }
            }
        }
        session()->flash("success", "Contact has been Successfully Updated!");
        return redirect('/admin/settings');
    }
    public function customer(array $data)
    {
        $customer = $data;
        foreach ($customer as $key => $value) {
            if ($key != '_token' && $key != 'name') {
                if (Application::where("name", $key)->count() == 0) {
                    Application::create(['name' => $key, "value" => $value, "group" => "Customer"]);
                } else {
                    Application::where("name", $key)->update(["value" => $value, "group" => "Customer"]);
                }
            }
        }
        session()->flash("success", "Customer has been Successfully Updated!");
        return redirect('/admin/settings');
    }
    public function commission(array $data)
    {
        $commission = $data;
        foreach ($commission as $key => $value) {
            if ($key != '_token' && $key != 'name') {
                if (Application::where("name", $key)->count() == 0) {
                    Application::create(['name' => $key, "value" => $value, "group" => "Commission"]);
                } else {
                    Application::where("name", $key)->update(["value" => $value, "group" => "Commission"]);
                }
            }
        }
        session()->flash("success", "Commission has been Successfully Updated!");
        return redirect('/admin/settings');
    }
    public function charges(array $data)
    {
        $charges = $data;
        foreach ($charges as $key => $value) {
            if ($key != '_token' && $key != 'name') {
                if (Application::where("name", $key)->count() == 0) {
                    Application::create(['name' => $key, "value" => $value, "group" => "Booking Charges"]);
                } else {
                    Application::where("name", $key)->update(["value" => $value, "group" => "Booking Charges"]);
                }
            }
        }
        session()->flash("success", "Booking Charges has been Successfully Updated!");
        return redirect('/admin/settings');
    }

    public function deliveryCharge(array $data)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['_token', 'name']) || empty($value)) {
                continue;
            }

            Application::updateOrCreate(
                [
                    'group' => 'Delivery Charge',
                ],
                [
                    'name' => $key,
                    'value' => $value,
                ]
            );
        }

        session()->flash("success", "Delivery Charge has been Successfully Created!");
        return redirect('/admin/settings');
    }

    public function freeDeliveryAmountAbove(array $data)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['_token', 'name']) || empty($value)) {
                continue;
            }

            Application::updateOrCreate(
                [
                    'group' => 'Free Delivery Amount Above',
                ],
                [
                    'name' => $key,
                    'value' => $value,
                ]
            );
        }

        session()->flash("success", "Free Delivery Amount Above has been Successfully Created!");
        return redirect('/admin/settings');
    }

    public function platformFee(array $data)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['_token', 'name']) || empty($value)) {
                continue;
            }

            Application::updateOrCreate(
                [
                    'group' => 'Platform Fee',
                ],
                [
                    'name' => $key,
                    'value' => $value,
                ]
            );
        }

        session()->flash("success", "Platform Fee has been Successfully Created!");
        return redirect('/admin/settings');
    }

    public function returnReplaceDaysSettings(array $data)
    {
        foreach ($data as $key => $value) {
            if (Application::where("name", $key)->count() == 0) {
                Application::create(['name' => $key, "value" => $value, "group" => "Return Replace Days"]);
            } else {
                Application::where("name", $key)->update(["value" => $value, "group" => "Return Replace Days"]);
            }
        }
        session()->flash("success", "Return & Replace days settings updated successfully!");
        return redirect('/admin/settings');
    }

    public function requestUserDelete($request)
    {
        $validated = $request->validate([
            'email' => 'required',
            'phone' => 'required',
            'username' => 'required',
        ],[
            'email.required' => 'Email is required.',
            'phone.required' => 'Phone number is required',
            'username' => 'User Name is required',
        ]);
        $mailData = [
            'username' => $request->username,
            'phone' => $request->phone,
            'email' => $request->email,
            'reason' => $request->reason
        ];
        Mail::to(config('mail.from.address'))->send(new UserDeleteMail($mailData));

        session()->flash("success", "Thank you trying our app. We will remove your data. It usually takes around 2-3 days.");
        return redirect('/user-data-remove');
    }
}
