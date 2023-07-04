<?php use App\User; ?>
@extends('layouts.frontLayout.front_design')
@section('content')
<div id="right_container">
    <div style="padding:20px 15px 30px 15px;">
        <h1>Sent Messages</h1>
        <table id="responses" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Name</th>
                <th>Location</th>
                <th>Message</th>
                <th>Date/Time</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sent_msg as $msg)
            <?php
            $receiver_name = User::getName($msg->receiver_id);
            $receiver_city = User::getCity($msg->receiver_id);
            ?>
            <tr align="center">
                @if($msg->seen==0)
                    <?php $bold_msg = 'style=font-weight:bold;'; ?>
                @else
                    <?php $bold_msg = 'style=font-weight:normal;'; ?>
                @endif
                <td {{ $bold_msg }}>{{ $receiver_name }}</td>
                <td {{ $bold_msg }}>{{ $receiver_city }}</td>
                <td {{ $bold_msg }}>{{ substr($msg->message,0,15) }}<a title="View Details" href="#messageDetails{{ $msg->id }}" data-toggle="modal">...</a></td>
                <td {{ $bold_msg }}>{{ $msg->created_at }}</td>
                <td {{ $bold_msg }}>
                    <a title="View Details" href="#messageDetails{{ $msg->id }}" data-toggle="modal"><i class="fa fa-file-text-o" aria-hidden="true"></i></a>&nbsp;
                    <div id="messageDetails{{ $msg->id }}" class="modal hide">
                        <div class="modal-header">
                            <button data-dismiss="modal" class="close" type="button">×</button>
                            <strong>Message Details</strong>
                            @if($msg->seen==1)
                                (Seen)
                            @else
                                (Unseen)
                            @endif
                        </div>
                        <div class="modal-body">
                            <p><?php echo nl2br($msg->message); ?></p>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    <div class="clear"></div>
</div>
@endsection