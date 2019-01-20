<?php

namespace App\Admin\Controllers;

use App\Models\CouponCode;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class CouponCodesController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('优惠券列表')
            ->body($this->grid());
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('编辑优惠券')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('新建优惠券')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CouponCode);

        $grid->model()->orderBy('created_at', 'desc');
        $grid->id('Id')->sortable();
        $grid->name('名称');
        $grid->code('优惠码');

        $grid->description('描述');

        $grid->column('usage', '用量')->display(function ($value) {
            return "{$this->used} / {$this->total}";
        });

        $grid->enabled('已启用？')->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->created_at('创建于');

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CouponCode);

        $form->text('name', '名称')->rules('required');
        $form->text('code', '优惠码')->rules(function ($form) {
            if ($id = $form->model()->id) {
                return 'nullable|unique:coupon_codes,code,'.$id.',id';
            } else {
                return 'nullable|unique:coupon_codes';
            }
        });
        $form->radio('type', '类型')->options(CouponCode::$typeMap)->rules('required');
        $form->decimal('value', '折扣')->rules(function ($form) {
            if ($form->model()->type === CouponCode::TYPE_PERCENT) {
                return 'required|numeric|between:1,99';
            } else {
                return 'required|numeric|min:0.01';
            }
        });
        $form->number('total', '总量')->rules('required|numeric|min:0');
        $form->decimal('min_amount', '最低金额')->rules('required|numeric|min:0.01');
        $form->datetime('not_before', '开始时间');
        $form->datetime('not_after', '结束时间');
        $form->switch('enabled', '启用');

        $form->saving(function (Form $form) {
            if (! $form->code) {
                $form->code = CouponCode::findAvailableCode();
            }
        });

        return $form;
    }
}
