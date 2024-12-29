CREATE TABLE users (
    uid INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profileImagePath VARCHAR(255),
    profileBio TEXT,
    accountLocation VARCHAR(255),
    allowComments BOOLEAN DEFAULT TRUE,
    voteCount INT DEFAULT 0,
    votes JSON,
    joinedForums JSON,
    verificationCode VARCHAR(255),
    verified BOOLEAN DEFAULT FALSE,
    date DATE
);

CREATE TABLE forums (
    fid INT AUTO_INCREMENT PRIMARY KEY,
    owner INT,
    name VARCHAR(255) NOT NULL UNIQUE,
    iconPath VARCHAR(255),
    description TEXT,
    date DATE,
    members JSON,
    mods JSON,
    bans JSON,
    FOREIGN KEY (owner) REFERENCES users(uid)
);

CREATE TABLE forumPosts (
    pid INT AUTO_INCREMENT PRIMARY KEY,
    fid INT,
    uid INT,
    title VARCHAR(255),
    body TEXT,
    voteCount INT DEFAULT 0,
    date DATE,
    votes JSON,
    commentCount INT DEFAULT 0,
    FOREIGN KEY (fid) REFERENCES forums(fid),
    FOREIGN KEY (uid) REFERENCES users(uid)
);

CREATE TABLE comments (
    cid INT AUTO_INCREMENT PRIMARY KEY,
    uid INT,
    commenterId INT,
    content TEXT,
    postDate DATE,
    likes INT DEFAULT 0,
    usersLiked JSON,
    usersReplied JSON,
    repliesCount INT DEFAULT 0,
    type ENUM('profile', 'forumpost'),
    FOREIGN KEY (uid) REFERENCES users(uid),
    FOREIGN KEY (commenterId) REFERENCES users(uid)
);

CREATE TABLE notifications (
    nid INT AUTO_INCREMENT PRIMARY KEY,
    uid INT,
    message TEXT,
    subMessage TEXT,
    date DATE,
    FOREIGN KEY (uid) REFERENCES users(uid)
);