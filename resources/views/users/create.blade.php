@extends('users.layout')

@section('content')

<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Add New User</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-primary" href="{{ route('users.index') }}"> Back</a>
        </div>
    </div>
</div>

@if ($errors->any())
<div class="alert alert-danger">
    <strong>Whoops!</strong> There were some problems with your input.<br><br>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <strong>First Name:</strong>
                <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <strong>Last Name:</strong>
                <input type="text" name="last_name" class="form-control" placeholder="Last Name">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <strong>employee_id:</strong>
                <input type="text" name="employee_id" class="form-control" placeholder="employee_id" required>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <strong>Email:</strong>
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <strong>Phone Number:</strong>
                <input type="text" name="phone_number" class="form-control" placeholder="Phone Number">
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <strong>Department:</strong>
                <select name="department" class="form-control" required>
                    <option value="" disabled selected>Select Department</option>
                    <option value="HR">HR</option>
                    <option value="Finance">Finance</option>
                    <option value="IT">IT</option>
                    <option value="Marketing">Marketing</option>
                    <option value="Sales">Sales</option>
                    <option value="Operations">Operations</option>
                </select>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <strong>Designation:</strong>
                <select name="designation" class="form-control">
                    <option value="" disabled selected>Select Designation</option>
                    <option value="Manager">Manager</option>
                    <option value="Team Lead">Team Lead</option>
                    <option value="Software Engineer">Software Engineer</option>
                    <option value="HR Executive">HR Executive</option>
                    <option value="Finance Analyst">Finance Analyst</option>
                    <option value="Marketing Specialist">Marketing Specialist</option>
                </select>
            </div>
        </div>


        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <strong>Joining Date:</strong>
                <input type="date" name="joining_date" class="form-control">
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Skill Set:</strong>
                <select name="skill_set[]" class="form-control" multiple >
                    <option value="PHP">PHP</option>
                    <option value="Laravel">Laravel</option>
                    <option value="JavaScript">JavaScript</option>
                    <option value="ReactJS">ReactJS</option>
                    <option value="VueJS">VueJS</option>
                    <option value="NodeJS">NodeJS</option>
                    <option value="Python">Python</option>
                    <option value="Django">Django</option>
                    <option value="SQL">SQL</option>
                    <option value="DevOps">DevOps</option>
                </select>
            </div>
        </div>


        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </div>
</form>

@endsection