-- PostgreSQL Database Version-- Create custom ENUM types first
CREATE TYPE user_status AS ENUM ('active', 'deactivated', 'suspended', 'deleted');
CREATE TYPE admin_role AS ENUM ('superadmin', 'moderator');
CREATE TYPE session_status AS ENUM ('active', 'inactive');
CREATE TYPE message_status AS ENUM ('pending', 'in_progress', 'resolved', 'archived');
CREATE TYPE user_type AS ENUM ('student', 'therapist', 'admin');

-- 1. Base Tables
CREATE TABLE students (
    srcode INTEGER PRIMARY KEY,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    phonenum CHAR(11) NOT NULL UNIQUE,
    email VARCHAR(150) UNIQUE NOT NULL,
    password CHAR(64) NOT NULL,
    department VARCHAR(100),
    year INTEGER,
    section VARCHAR(10),
    course VARCHAR(100),
    address TEXT,
    personality TEXT,
    status user_status DEFAULT 'active',
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    username VARCHAR(150) UNIQUE,
    deleted_at TIMESTAMP,
    delete_reason TEXT CHECK (delete_reason IN ('privacy', 'not_useful', 'other')),
    delete_details TEXT,
    CONSTRAINT valid_phone CHECK (phonenum ~ '^09\d{9}$')
);

CREATE TABLE therapists (
    therapist_id VARCHAR(10) PRIMARY KEY,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password CHAR(64) NOT NULL,
    specialization VARCHAR(255),
    license_number VARCHAR(50) UNIQUE NOT NULL,
    contact_number CHAR(11) UNIQUE NOT NULL,
    dob DATE NOT NULL,
    age INTEGER GENERATED ALWAYS AS (EXTRACT(YEAR FROM CURRENT_DATE) - EXTRACT(YEAR FROM dob)) STORED,
    status user_status DEFAULT 'active',
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    username VARCHAR(150) UNIQUE,
    CONSTRAINT valid_contact CHECK (contact_number ~ '^09\d{9}$')
);

CREATE TABLE admins (
    admin_id VARCHAR(10) PRIMARY KEY,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password CHAR(64) NOT NULL,
    contact_number CHAR(11) UNIQUE NOT NULL,
    role admin_role DEFAULT 'moderator',
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT valid_contact CHECK (contact_number ~ '^09\d{9}$')
);

-- Session Logging Tables
CREATE TABLE session_logs (
    session_id SERIAL PRIMARY KEY,
    srcode INTEGER REFERENCES students(srcode) ON DELETE CASCADE,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP,
    ip_address VARCHAR(45),
    session_status session_status DEFAULT 'active'
);

CREATE TABLE admin_session_logs (
    session_id SERIAL PRIMARY KEY,
    admin_id VARCHAR(10) REFERENCES admins(admin_id) ON DELETE CASCADE,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP,
    ip_address VARCHAR(45),
    session_status session_status DEFAULT 'active'
);

CREATE TABLE therapist_session_logs (
    session_id SERIAL PRIMARY KEY,
    therapist_id VARCHAR(10) REFERENCES therapists(therapist_id) ON DELETE CASCADE,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP,
    ip_address VARCHAR(45),
    session_status session_status DEFAULT 'active'
);

-- Activity Logging Tables
CREATE TABLE activity_logs (
    log_id SERIAL PRIMARY KEY,
    srcode INTEGER NOT NULL REFERENCES students(srcode) ON DELETE CASCADE,
    action VARCHAR(255) NOT NULL,
    action_details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admin_activity_logs (
    log_id SERIAL PRIMARY KEY,
    admin_id VARCHAR(10) REFERENCES admins(admin_id) ON DELETE CASCADE,
    action VARCHAR(255) NOT NULL,
    action_details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE therapist_activity_logs (
    log_id SERIAL PRIMARY KEY,
    therapist_id VARCHAR(10) REFERENCES therapists(therapist_id) ON DELETE CASCADE,
    action VARCHAR(255) NOT NULL,
    action_details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Feature Tables
CREATE TABLE moodlog (
    moodlog_id VARCHAR(10) PRIMARY KEY,
    srcode INTEGER NOT NULL REFERENCES students(srcode) ON DELETE CASCADE,
    selected_emoji VARCHAR(50) NOT NULL,
    mood_name VARCHAR(100) NOT NULL,
    description TEXT,
    log_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE mood_streaks (
    streak_id SERIAL PRIMARY KEY,
    srcode INTEGER NOT NULL REFERENCES students(srcode) ON DELETE CASCADE,
    current_streak INTEGER DEFAULT 0
);

CREATE TYPE notification_type AS ENUM ('info', 'warning', 'success', 'error');
CREATE TABLE notifications (
    id SERIAL PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    type notification_type NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    icon VARCHAR(50) NOT NULL,
    color_class VARCHAR(50) NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE quotes (
    id SERIAL PRIMARY KEY,
    content TEXT NOT NULL,
    author VARCHAR(255) NOT NULL,
    category VARCHAR(100) DEFAULT 'mental health',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE journal_entries (
    entry_id SERIAL PRIMARY KEY,
    srcode INTEGER NOT NULL REFERENCES students(srcode) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    mood VARCHAR(50) NOT NULL,
    entry_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE profile_pictures (
    id SERIAL PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    user_type user_type NOT NULL,
    image_data BYTEA NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    status user_status DEFAULT 'active',
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, user_type)
);

CREATE TABLE contacts (
    id SERIAL PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status message_status DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reminder_settings (
    id SERIAL PRIMARY KEY,
    user_id VARCHAR(50),
    reminder_time TIME DEFAULT '17:00:00',
    is_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE password_resets (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    used BOOLEAN DEFAULT FALSE
);

CREATE TABLE email_verifications (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    verification_code VARCHAR(6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    is_verified BOOLEAN DEFAULT FALSE
);

CREATE TYPE post_type AS ENUM ('student', 'therapist');
CREATE TYPE post_status AS ENUM ('active', 'hidden', 'deleted');

CREATE TABLE posts (
    post_id SERIAL PRIMARY KEY,
    username VARCHAR(150) NOT NULL,
    content TEXT NOT NULL,
    image_file BYTEA,
    image_name VARCHAR(255),
    post_type post_type NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status post_status DEFAULT 'active'
);

CREATE TABLE comments (
    comment_id SERIAL PRIMARY KEY,
    post_id INTEGER NOT NULL REFERENCES posts(post_id) ON DELETE CASCADE,
    username VARCHAR(150) NOT NULL,
    comment_text TEXT NOT NULL,
    commenter_type post_type NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status post_status DEFAULT 'active'
);

CREATE TABLE likes (
    like_id SERIAL PRIMARY KEY,
    post_id INTEGER NOT NULL REFERENCES posts(post_id) ON DELETE CASCADE,
    username VARCHAR(150) NOT NULL,
    liker_type post_type NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (post_id, username, liker_type)
);

CREATE TYPE report_type AS ENUM (
    'inappropriate',
    'harassment',
    'spam',
    'hate_speech',
    'violence',
    'fake',
    'other'
);

CREATE TYPE report_status AS ENUM ('pending', 'reviewed', 'resolved');
CREATE TYPE reported_type AS ENUM ('post', 'user');

CREATE TABLE reports (
    report_id SERIAL PRIMARY KEY,
    reporter_username VARCHAR(150) NOT NULL,
    reporter_type post_type NOT NULL,
    reported_type reported_type NOT NULL,
    reported_id VARCHAR(150) NOT NULL,
    report_type report_type NOT NULL,
    reason TEXT,
    status report_status DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Support & Applications
CREATE TABLE support_messages (
    id SERIAL PRIMARY KEY,
    srcode INTEGER NOT NULL REFERENCES students(srcode) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    attachment_data BYTEA,
    attachment_name VARCHAR(255),
    attachment_type VARCHAR(100),
    status message_status DEFAULT 'pending',
    reply_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE therapist_support_messages (
    id SERIAL PRIMARY KEY,
    therapist_id VARCHAR(10) NOT NULL REFERENCES therapists(therapist_id) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    attachment_data BYTEA,
    attachment_name VARCHAR(255),
    attachment_type VARCHAR(100),
    status message_status DEFAULT 'pending',
    reply_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE support_replies (
    reply_id SERIAL PRIMARY KEY,
    message_id INTEGER NOT NULL REFERENCES support_messages(id) ON DELETE CASCADE,
    admin_id VARCHAR(10) NOT NULL REFERENCES admins(admin_id),
    reply_text TEXT NOT NULL,
    attachment_name VARCHAR(255),
    attachment_type VARCHAR(100),
    attachment_data BYTEA,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE therapist_support_replies (
    reply_id SERIAL PRIMARY KEY,
    message_id INTEGER NOT NULL REFERENCES therapist_support_messages(id) ON DELETE CASCADE,
    admin_id VARCHAR(10) NOT NULL REFERENCES admins(admin_id),
    reply_text TEXT NOT NULL,
    attachment_name VARCHAR(255),
    attachment_type VARCHAR(100),
    attachment_data BYTEA,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TYPE application_status AS ENUM ('pending', 'approved', 'rejected');
CREATE TABLE therapist_applications (
    id SERIAL PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    license_number VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    experience TEXT NOT NULL,
    license_file BYTEA,
    license_file_type VARCHAR(50),
    resume BYTEA,
    resume_file_type VARCHAR(50),
    profile_picture BYTEA,
    profile_picture_type VARCHAR(50),
    status application_status DEFAULT 'pending',
    application_date TIMESTAMP NOT NULL,
    review_date TIMESTAMP,
    review_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Therapy Sessions
CREATE TYPE session_type AS ENUM ('online', 'face-to-face');
CREATE TYPE therapy_status AS ENUM ('pending', 'confirmed', 'completed', 'cancelled');
CREATE TYPE video_status AS ENUM ('scheduled', 'ongoing', 'completed', 'cancelled');

CREATE TABLE therapy_sessions (
    session_id SERIAL PRIMARY KEY,
    srcode INTEGER NOT NULL REFERENCES students(srcode),
    therapist_id VARCHAR(10) NOT NULL REFERENCES therapists(therapist_id),
    session_date DATE NOT NULL,
    session_time TIME NOT NULL,
    session_type session_type NOT NULL,
    status therapy_status DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE video_therapy_sessions (
    id SERIAL PRIMARY KEY,
    therapy_session_id INTEGER REFERENCES therapy_sessions(session_id),
    meeting_id VARCHAR(255) NOT NULL,
    channel_name VARCHAR(255) NOT NULL,
    therapist_id VARCHAR(10) NOT NULL REFERENCES therapists(therapist_id),
    srcode INTEGER NOT NULL REFERENCES students(srcode),
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP,
    duration INTEGER,
    status video_status DEFAULT 'scheduled',
    recording_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE session_feedback (
    feedback_id SERIAL PRIMARY KEY,
    session_id INTEGER NOT NULL REFERENCES therapy_sessions(session_id),
    diagnosis TEXT,
    recommendations TEXT,
    follow_up BOOLEAN DEFAULT FALSE,
    follow_up_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE status_notifications (
    id SERIAL PRIMARY KEY,
    session_id INTEGER NOT NULL REFERENCES therapy_sessions(session_id),
    old_status therapy_status,
    new_status therapy_status,
    processed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sms_logs (
    id SERIAL PRIMARY KEY,
    booking_id INTEGER REFERENCES therapy_sessions(session_id),
    recipient_number VARCHAR(20),
    message_text TEXT,
    message_type therapy_status,
    message_id VARCHAR(100),
    status VARCHAR(50),
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    error_message TEXT
);

CREATE TYPE module_category AS ENUM ('Meditation', 'Exercise', 'Mindfulness', 'Stress Management', 'Sleep', 'Other');
CREATE TYPE difficulty_level AS ENUM ('Beginner', 'Intermediate', 'Advanced');
CREATE TYPE module_status AS ENUM ('active', 'inactive', 'archived');

CREATE TABLE self_care_modules (
    module_id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category module_category NOT NULL,
    difficulty_level difficulty_level DEFAULT 'Beginner',
    estimated_duration VARCHAR(50),
    thumbnail BYTEA,
    thumbnail_type VARCHAR(50),
    content_file BYTEA,
    content_file_type VARCHAR(50),
    status module_status DEFAULT 'active',
    created_by VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE system_errors (
    id BIGSERIAL PRIMARY KEY,
    error_message TEXT NOT NULL,
    error_context TEXT,
    stack_trace TEXT,
    severity VARCHAR(20) NOT NULL DEFAULT 'ERROR',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP,
    resolution_notes TEXT
);

-- Function to generate student username
CREATE OR REPLACE FUNCTION generate_student_username()
RETURNS TRIGGER AS $$
DECLARE
    creation_date TEXT;
    base_username TEXT;
    existing_count INTEGER;
BEGIN
    creation_date := TO_CHAR(NEW.created_date, 'YYYYMMDD');
    base_username := CONCAT('space_', LOWER(NEW.lastname), '_', creation_date);
    SELECT COUNT(*) INTO existing_count FROM students WHERE username LIKE base_username || '%';
    NEW.username := CONCAT(base_username, existing_count + 1);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER generate_student_username_trigger
BEFORE INSERT ON students
FOR EACH ROW
EXECUTE FUNCTION generate_student_username();

-- Function to generate therapist ID and username
CREATE OR REPLACE FUNCTION generate_therapist_id_and_username()
RETURNS TRIGGER AS $$
DECLARE
    count_id INTEGER;
    new_id TEXT;
BEGIN
    SELECT COUNT(*) + 1 INTO count_id FROM therapists;
    new_id := CONCAT('THR-', LPAD(count_id::TEXT, 3, '0'));
    NEW.therapist_id := new_id;
    NEW.username := CONCAT('space_', new_id);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER generate_therapist_id_and_username_trigger
BEFORE INSERT ON therapists
FOR EACH ROW
EXECUTE FUNCTION generate_therapist_id_and_username();

-- Function to generate admin ID
CREATE OR REPLACE FUNCTION generate_admin_id()
RETURNS TRIGGER AS $$
DECLARE
    count_id INTEGER;
    new_id TEXT;
BEGIN
    SELECT COUNT(*) + 1 INTO count_id FROM admins;
    new_id := CONCAT('ADM-', LPAD(count_id::TEXT, 4, '0'));
    NEW.admin_id := new_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER generate_admin_id_trigger
BEFORE INSERT ON admins
FOR EACH ROW
EXECUTE FUNCTION generate_admin_id();

-- Function to update reply counts
CREATE OR REPLACE FUNCTION update_support_reply_count()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE support_messages 
    SET reply_count = reply_count + 1 
    WHERE id = NEW.message_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER after_support_reply_insert_trigger
AFTER INSERT ON support_replies
FOR EACH ROW
EXECUTE FUNCTION update_support_reply_count();

CREATE OR REPLACE FUNCTION update_therapist_support_reply_count()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE therapist_support_messages 
    SET reply_count = reply_count + 1 
    WHERE id = NEW.message_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER after_therapist_reply_insert_trigger
AFTER INSERT ON therapist_support_replies
FOR EACH ROW
EXECUTE FUNCTION update_therapist_support_reply_count();

-- Function to track therapy session status changes
CREATE OR REPLACE FUNCTION track_therapy_session_status()
RETURNS TRIGGER AS $$
BEGIN
    IF OLD.status != NEW.status AND NEW.status IN ('confirmed', 'cancelled') THEN
        INSERT INTO status_notifications 
            (session_id, old_status, new_status)
        VALUES 
            (NEW.session_id, OLD.status, NEW.status);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER after_therapy_session_update_trigger
AFTER UPDATE ON therapy_sessions
FOR EACH ROW
EXECUTE FUNCTION track_therapy_session_status();

