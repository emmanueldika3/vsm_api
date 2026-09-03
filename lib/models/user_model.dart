// lib/models/user_model.dart

enum UserRole { admin, player, treasurer, president, coach }

// 🟢 EXTENSION SUR L'ENUM : Permet de récupérer facilement les libellés partout
extension UserRoleExtension on UserRole {
  /// Libellé court en majuscules pour les titres de tableaux de bord (ex: "ESPACE JOUEUR")
  String get label {
    switch (this) {
      case UserRole.admin:
        return 'ADMINISTRATEUR';
      case UserRole.president:
        return 'PRÉSIDENT';
      case UserRole.coach:
        return 'ENTRAÎNEUR';
      case UserRole.treasurer:
        return 'TRÉSORIER';
      case UserRole.player:
      default:
        return 'JOUEUR';
    }
  }

  /// Libellé complet avec fonction/titre dans le club
  String get title {
    switch (this) {
      case UserRole.admin:
        return 'Capitaine / Admin';
      case UserRole.treasurer:
        return 'Trésorier';
      case UserRole.president:
        return 'Président';
      case UserRole.coach:
        return 'Coach / Entraîneur';
      case UserRole.player:
      default:
        return 'Joueur VSM PK11';
    }
  }
}

class UserModel {
  final String id;
  final String fullName;
  final String email;
  final String phone;
  final String? photoUrl;
  final String position;
  final int? number;
  final UserRole role;
  String status; // 'present', 'late', 'absent'
  bool isStarter;

  UserModel({
    required this.id,
    required this.fullName,
    required this.email,
    required this.phone,
    this.photoUrl,
    this.position = 'Joueur',
    this.number,
    required this.role,
    this.status = 'absent',
    this.isStarter = false,
  });

  // 🟢 GETTERS UI & RÔLES
  String get roleName => role.name;

  /// Libellé lisible du rôle (ex: "Capitaine / Admin", "Joueur VSM PK11")
  String get roleTitle => role.title;

  /// Libellé en majuscules pour les titres d'onglets / tableaux de bord (ex: "JOUEUR")
  String get roleLabel => role.label;

  // 🟢 COPYWITH
  UserModel copyWith({
    String? id,
    String? fullName,
    String? email,
    String? phone,
    String? photoUrl,
    String? position,
    int? number,
    UserRole? role,
    String? status,
    bool? isStarter,
  }) {
    return UserModel(
      id: id ?? this.id,
      fullName: fullName ?? this.fullName,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      photoUrl: photoUrl ?? this.photoUrl,
      position: position ?? this.position,
      number: number ?? this.number,
      role: role ?? this.role,
      status: status ?? this.status,
      isStarter: isStarter ?? this.isStarter,
    );
  }

  // 🟢 DESERIALISATION : Convertit le JSON Laravel en UserModel
  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'].toString(),
      fullName: json['name'] ?? json['full_name'] ?? json['fullName'] ?? '',
      email: json['email'] ?? '',
      phone: json['phone'] ?? json['telephone'] ?? '',
      photoUrl: json['photo_url'] ?? json['avatar'] ?? json['photo'],
      position: json['position'] ?? 'Joueur',
      number: json['number'] != null
          ? int.tryParse(json['number'].toString())
          : null,
      role: _roleFromString(json['role']?.toString() ?? 'player'),
      status: json['status'] ?? json['attendance']?['status'] ?? 'absent',
      isStarter:
          json['is_starter'] ?? json['attendance']?['is_starter'] ?? false,
    );
  }

  // 🟢 SERIALISATION : Convertit UserModel en Map JSON
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': fullName,
      'email': email,
      'phone': phone,
      'photo_url': photoUrl,
      'position': position,
      'number': number,
      'role': role.name,
      'status': status,
      'is_starter': isStarter,
    };
  }

  // Helper pour convertir la chaîne API vers l'enum UserRole
  static UserRole _roleFromString(String roleStr) {
    final String cleanRole = roleStr.toLowerCase().trim();

    switch (cleanRole) {
      case 'admin':
      case 'capitaine / admin':
        return UserRole.admin;
      case 'treasurer':
      case 'tresorier':
        return UserRole.treasurer;
      case 'president':
        return UserRole.president;
      case 'coach':
      case 'entraineur':
      case 'encadreur':
        return UserRole.coach;
      case 'member':
      case 'player':
      case 'joueur':
      case 'veteran':
      default:
        return UserRole.player;
    }
  }
}
