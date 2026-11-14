import 'package:dio/dio.dart';

/// Erreur d'API normalisée, construite à partir du format JSON de Laravel :
/// `{ "message": "...", "errors": { "champ": ["..."] } }`.
class ApiException implements Exception {
  ApiException(this.message, {this.statusCode, this.fieldErrors = const {}});

  final String message;
  final int? statusCode;
  final Map<String, List<String>> fieldErrors;

  bool get isUnauthenticated => statusCode == 401;
  bool get isNetworkError => statusCode == null;

  String? firstErrorFor(String field) => fieldErrors[field]?.firstOrNull;

  factory ApiException.fromDio(DioException e) {
    final response = e.response;
    if (response == null) {
      return ApiException('Pas de connexion internet. Vérifie ton réseau et réessaie.');
    }

    final data = response.data;
    final status = response.statusCode;
    var message = switch (status) {
      401 => 'Ta session a expiré. Reconnecte-toi.',
      403 => 'Accès non autorisé.',
      429 => 'Trop de tentatives. Patiente un instant avant de réessayer.',
      final s? when s >= 500 => 'Le service est momentanément indisponible.',
      _ => 'Une erreur est survenue.',
    };
    final fields = <String, List<String>>{};

    if (data is Map) {
      if (status == 422 && data['message'] is String) message = data['message'] as String;
      final errors = data['errors'];
      if (errors is Map) {
        errors.forEach((key, value) {
          if (value is List) fields[key.toString()] = value.map((v) => v.toString()).toList();
        });
      }
    }

    return ApiException(message, statusCode: status, fieldErrors: fields);
  }

  @override
  String toString() => 'ApiException($statusCode): $message';
}
