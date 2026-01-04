import React, { useState, useEffect, useRef } from 'react';
import Pusher from 'pusher-js';
import classNames from 'classnames/bind';
import styles from './ChatAdmin.module.scss';
import axios from 'axios';
import { Send, Search, Circle, X } from 'lucide-react';
import { toast } from 'react-toastify';

const cx = classNames.bind(styles);

const ChatAdmin = ({ adminId, token, members }) => {
    const [selectedUser, setSelectedUser] = useState(null);
    const [messages, setMessages] = useState([]);
    const [newMessage, setNewMessage] = useState('');
    const [conversations, setConversations] = useState([]);
    const [searchTerm, setSearchTerm] = useState('');
    const [loading, setLoading] = useState(false);
    const messagesEndRef = useRef(null);
    const pusherRef = useRef(null);
    const channelRef = useRef(null);

    const API_URL = 'http://localhost:8000/api';

    const axiosInstance = axios.create({
        baseURL: API_URL,
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
            'Content-Type': 'application/json',
        },
    });

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    useEffect(() => {
        if (!adminId) return;
        if (pusherRef.current) return;

        console.log('🔌 Initializing Pusher ONCE for Admin ID:', adminId);

        pusherRef.current = new Pusher('bf2c2c6cc1b747ed5016', {
            cluster: 'ap1',
            authEndpoint: 'http://localhost:8000/broadcasting/auth',
            auth: {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json',
                },
            },
        });
        const channelName = `private-chat.${adminId}`;

        channelRef.current = pusherRef.current.subscribe(channelName);

        channelRef.current.bind('pusher:subscription_succeeded', () => {
            console.log('Admin subscribed:', channelName);
        });
    }, []);

    useEffect(() => {
        if (!channelRef.current || !selectedUser) return;

        const handleMessageSent = (data) => {
            if (data.message.sender_id !== selectedUser.id) return;
            setMessages((prev) => {
                const exists = prev.some((m) => m.id === data.message.id);
                if (exists) return prev;
                return [...prev, data.message];
            });
        };

        channelRef.current.unbind('MessageSent');
        channelRef.current.bind('MessageSent', handleMessageSent);

        return () => {
            channelRef.current.unbind('MessageSent', handleMessageSent);
        };
    }, [selectedUser]);

    const loadConversations = async () => {
        try {
            const response = await axiosInstance.get('/conversations');
            setConversations(response.data);
        } catch (error) {
            console.error('Error loading conversations:', error);
            if (error.response?.status === 401) {
                localStorage.clear();
                toast.error('Token hết hạn hoặc không hợp lệ vui lòng đăng nhập lại');
                setTimeout(() => {
                    window.location.href = '/admin/login';
                }, 1500);
            }
        }
    };

    useEffect(() => {
        loadConversations();
    }, []);

    const loadChatHistory = async (userId) => {
        setLoading(true);
        try {
            const response = await axiosInstance.get(`/messages/${userId}`);
            setMessages(response.data);
        } catch (error) {
            if (error.response?.status === 404) {
                console.error('User không tồn tại');
            }
        } finally {
            setLoading(false);
        }
    };

    const sendMessage = async () => {
        if (!newMessage.trim() || !selectedUser) return;

        try {
            const response = await axiosInstance.post('/send-message', {
                receiver_id: selectedUser.id,
                message: newMessage.trim(),
            });
            setMessages((prev) => [...prev, response.data]);
            setNewMessage('');
            loadConversations();
        } catch (error) {
            console.error('Error sending message:', error);
            if (error.response?.status === 400) {
                alert(error.response.data.error || 'Không thể gửi tin nhắn');
            }
        }
    };

    const handleKeyPress = (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    };

    const selectUser = async (user) => {
        setSelectedUser(user);
        setMessages([]);
        await loadChatHistory(user.id);
    };

    const filteredMembers = members.filter(
        (member) =>
            member.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            member.email.toLowerCase().includes(searchTerm.toLowerCase()),
    );

    const getConversationInfo = (userId) => {
        const conv = conversations.find((c) => c.user.id === userId);
        return conv || { unread_count: 0 };
    };

    return (
        <div className={cx('chatContainer')}>
            <div className={cx('sidebar')}>
                <div className={cx('sidebarHeader')}>
                    <h2 className={cx('sidebarHeaderTitle')}>Tin nhắn</h2>
                    <div className={cx('searchWrapper')}>
                        <Search className={cx('searchIcon')} size={20} />
                        <input
                            type="text"
                            placeholder="Tìm kiếm thành viên..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className={cx('searchInput')}
                        />
                    </div>
                </div>

                <div className={cx('membersList')}>
                    {filteredMembers.map((member) => {
                        const convInfo = getConversationInfo(member.id);
                        return (
                            <div
                                key={member.id}
                                onClick={() => selectUser(member)}
                                className={cx('memberItem', { selected: selectedUser?.id === member.id })}
                            >
                                <div className={cx('memberInfo')}>
                                    <div className={cx('memberNameEmail')}>
                                        <h3 className={cx('memberName')}>{member.name}</h3>
                                        <p className={cx('memberEmail')}>{member.email}</p>
                                    </div>
                                    {convInfo.unread_count > 0 && (
                                        <span className={cx('unreadBadge')}>{convInfo.unread_count}</span>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>

            <div className={cx('chatArea')}>
                {selectedUser ? (
                    <>
                        <div className={cx('chatHeader')}>
                            <div className={cx('userInfo')}>
                                <div className={cx('userAvatar')}>{selectedUser.name.charAt(0)}</div>
                                <div className={cx('userNameEmail')}>
                                    <h3 className={cx('userName')}>{selectedUser.name}</h3>
                                    <p className={cx('userEmail')}>{selectedUser.email}</p>
                                </div>
                            </div>
                        </div>

                        <div className={cx('chatMessages')}>
                            {messages.length === 0 ? (
                                <div className={cx('noMessage')}>Chưa có tin nhắn nào</div>
                            ) : (
                                messages.map((msg) => (
                                    <div
                                        key={msg.id}
                                        className={cx('message', msg.sender_id == adminId ? 'sent' : 'received')}
                                    >
                                        <p>{msg.message}</p>
                                        <p
                                            className={cx(
                                                'time',
                                                msg.sender_id === adminId ? 'sentTime' : 'receivedTime',
                                            )}
                                        >
                                            {new Date(msg.created_at).toLocaleTimeString('vi-VN', {
                                                hour: '2-digit',
                                                minute: '2-digit',
                                            })}
                                        </p>
                                    </div>
                                ))
                            )}
                        </div>

                        <div className={cx('chatInput')}>
                            <input
                                type="text"
                                value={newMessage}
                                onChange={(e) => setNewMessage(e.target.value)}
                                onKeyPress={handleKeyPress}
                                placeholder="Nhập tin nhắn..."
                            />
                            <button onClick={sendMessage} disabled={!newMessage.trim()} className={cx('sendButton')}>
                                <Send size={20} />
                                <span>Gửi</span>
                            </button>
                        </div>
                    </>
                ) : (
                    <div className={cx('emptyChat')}>
                        <p>Chọn một thành viên để bắt đầu trò chuyện</p>
                    </div>
                )}
            </div>
        </div>
    );
};

export default ChatAdmin;
