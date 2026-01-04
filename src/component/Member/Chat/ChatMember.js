import React, { useState, useEffect, useRef } from 'react';
import Pusher from 'pusher-js';
import styles from './ChatMember.module.scss';
import classNames from 'classnames/bind';
import axios from 'axios';
import { Send, X, MessageCircle } from 'lucide-react';
import { toast } from 'react-toastify';

const cx = classNames.bind(styles);

const ChatMember = ({ userId, token, userAdmin }) => {
    const [messages, setMessages] = useState([]);
    const [newMessage, setNewMessage] = useState('');
    const [loading, setLoading] = useState(false);
    const [isOpen, setIsOpen] = useState(false);
    const [unreadCount, setUnreadCount] = useState(0);
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
        if (isOpen) {
            scrollToBottom();
        }
    }, [messages, isOpen]);

    useEffect(() => {
        if (!userId || !token) {
            if (pusherRef.current) {
                console.log('🔌 Disconnecting Pusher (Logged out)');
                pusherRef.current.disconnect();
                pusherRef.current = null;
                channelRef.current = null;
            }
            return;
        }

        if (pusherRef.current && pusherRef.current.initializedUserId === userId) {
            return;
        }

        if (pusherRef.current) {
            pusherRef.current.disconnect();
        }

        console.log('🔌 Initializing Pusher for User ID:', userId);

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
        pusherRef.current.initializedUserId = userId;

        const channelName = `private-chat.${userId}`;
        console.log('📡 Subscribing to channel:', channelName);

        channelRef.current = pusherRef.current.subscribe(channelName);

        channelRef.current.bind('pusher:subscription_succeeded', () => {
            console.log('Subscribed:', channelName);
        });

        loadUnreadCount();

        return () => {};
    }, [userId, token]);

    useEffect(() => {
        if (!channelRef.current) return;

        const handleMessageSent = (data) => {
            if (data.message.sender_id !== userAdmin) return;

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
    }, [userAdmin]);

    const loadUnreadCount = async () => {
        try {
            console.log('📡 Fetching unread count from:', `${API_URL}/unread-count`);
            const response = await axiosInstance.get('unread-count');
            setUnreadCount(response.data.unread_count);
            console.log('📬 Unread count:', response.data.unread_count);
        } catch (error) {
            console.error('❌ Error loading unread count:', error);
            if (error.response) {
                console.error('Error Status:', error.response.status);
                console.error('Error Data:', error.response.data);
            }
        }
    };

    const loadChatHistory = async () => {
        setLoading(true);
        try {
            console.log('📥 Loading chat history with admin:', userAdmin);
            const response = await axiosInstance.get(`/messages/${userAdmin}`);
            setMessages(response.data);
            setUnreadCount(0);
            console.log('✅ Loaded', response.data.length, 'messages');
        } catch (error) {
            console.error('Error loading chat history:', error);
            if (error.response?.status === 404) {
                console.error('User không tồn tại');
            }
        } finally {
            setLoading(false);
        }
    };

    const sendMessage = async () => {
        if (!newMessage.trim()) return;

        try {
            const response = await axiosInstance.post('/send-message', {
                receiver_id: userAdmin,
                message: newMessage.trim(),
            });
            setMessages((prev) => [...prev, response.data]);
            setNewMessage('');
        } catch (error) {
            console.error('Error sending message:', error);
            if (error.response?.status === 400) {
                alert(error.response.data.error || 'Không thể gửi tin nhắn');
            } else if (error.response?.status === 401) {
                localStorage.clear();
                toast.error('Phiên đăng nhập đã hết hạn vui lòng đăng nhập lại');
                setTimeout(() => {
                    window.location.href = '/';
                }, 1500);
            }
        }
    };

    const handleKeyPress = (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    };

    const toggleChat = () => {
        console.log('💬 Toggling chat. Current state:', isOpen);
        if (!isOpen) {
            loadChatHistory();
        }
        setIsOpen(!isOpen);
    };

    return (
        <>
            {!isOpen && (
                <button className={cx('chatButton')} onClick={toggleChat}>
                    <MessageCircle size={28} />
                    {unreadCount > 0 && (
                        <span className={cx('unreadBadge')}>{unreadCount > 99 ? '99+' : unreadCount}</span>
                    )}
                </button>
            )}
            {isOpen && (
                <div className={cx('chatWindow')}>
                    <div className={cx('chatHeader')}>
                        <div className={cx('chatHeaderLeft')}>
                            <div className={cx('chatHeaderName')}>
                                <p>Admin - Hỗ trợ khách hàng</p>
                            </div>
                        </div>
                        <button className={cx('chatHeaderButton')} onClick={toggleChat}>
                            <X size={20} />
                        </button>
                    </div>
                    <div className={cx('messages')}>
                        {loading ? (
                            <div className={cx('loading')}>
                                <div className={cx('spinner')}></div>
                            </div>
                        ) : messages.length === 0 ? (
                            <div className={cx('noMessages')}>
                                <MessageCircle size={48} className={cx('noMessagesIcon')} />
                                <p>
                                    Chưa có tin nhắn nào.
                                    <br />
                                    Gửi tin nhắn để bắt đầu trò chuyện!
                                </p>
                            </div>
                        ) : (
                            messages.map((msg) => (
                                <div
                                    key={msg.id}
                                    className={cx('messageItem', msg.sender_id == userId ? 'user' : 'admin')}
                                >
                                    <div className={cx('messageContent')}>
                                        <p>{msg.message}</p>
                                        <span className={cx('messageTime')}>
                                            {new Date(msg.created_at).toLocaleTimeString('vi-VN', {
                                                hour: '2-digit',
                                                minute: '2-digit',
                                            })}
                                        </span>
                                    </div>
                                </div>
                            ))
                        )}
                        <div ref={messagesEndRef} />
                    </div>
                    <div className={cx('messageInputWrapper')}>
                        <div className={cx('messageInputInner')}>
                            <input
                                type="text"
                                className={cx('messageInput')}
                                placeholder="Nhập tin nhắn..."
                                value={newMessage}
                                onChange={(e) => setNewMessage(e.target.value)}
                                onKeyDown={handleKeyPress}
                            />
                            <button
                                className={cx('messageSendButton')}
                                onClick={sendMessage}
                                disabled={!newMessage.trim()}
                            >
                                <Send size={20} />
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
};

export default ChatMember;
