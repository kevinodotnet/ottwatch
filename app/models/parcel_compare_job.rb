class ParcelCompareJob < ApplicationJob
  SKIP_ATTRS = ["id", "objectid", "snapshot_date", "created_at", "updated_at"]
  def perform
    (s1, s2) = Parcel
      .select(:snapshot_date)
      .distinct(:snapshot_date)
      .order(:snapshot_date)
      .last(2)
      .map(&:snapshot_date)    

    # s2 = Parcel.maximum(:snapshot_date);
    # s1 = Parcel.where(snapshot_date: ...s2).maximum(:snapshot_date);

    # iterate by pins
    pin_id = -1
    pin_id = "039250122"

    while !pin_id.nil?
      p1 = Parcel.select(:pin).where(snapshot_date: s1).where("pin > ?", pin_id).order(:pin).limit(1).first
      p2 = Parcel.select(:pin).where(snapshot_date: s2).where("pin > ?", pin_id).order(:pin).limit(1).first
      pin_id = [p1.pin, p2.pin].min
      compare_pin(pin_id, s1, s2)
    end
  end

  def compare_pin(pin_id, s1, s2)
    p1 = Parcel.where(snapshot_date: s1, pin: pin_id).order(:id).to_a
    p2 = Parcel.where(snapshot_date: s2, pin: pin_id).order(:id).to_a

    if p1.count != p2.count
      if p1.count == 1 && p2.count == 0
        return :removed
      end
      if p1.count == 0 && p2.count == 1
        return :added
      end
      binding.pry
    end

    result = p1.each_with_index.map do |p, i|
      compare_parcels(p, p2[i])
    end
    return result.uniq.first if result.uniq.count == 1
    # return :equal if result.uniq == [:equal]
    binding.pry
  end

  def compare_parcels(p1, p2)
    p1a = p1.attributes.except(*SKIP_ATTRS)
    p2a = p2.attributes.except(*SKIP_ATTRS)

    return :equal if p1a == p2a

    diff_keys = p1a.keys.select{|k| p1a[k] != p2a[k]}
    return diff_keys.join(":").to_sym

    # return :attr_change_non_geo if diff_keys == ["postal_code"]
    # return :attr_change_geo_only if diff_keys == ["shape_length", "shape_area", "geometry_json"]
    # return :attr_change_geo_only if diff_keys == ["northing", "shape_length", "shape_area", "geometry_json"]
    # return :attr_change_geo_only if diff_keys == ["geometry_json"]
    # return :attr_change_geo_only if diff_keys == ["easting", "northing", "shape_length", "shape_area", "geometry_json"]

    # return :attr_change_non_geo if diff_keys == ["pi_municipal_address_id", "record_owner_id", "rt_road_name_id", "address_number", "road_name", "suffix", "dir", "municipality_name", "postal_code", "address_status", "address_type_id", "pin_number", "feat_num", "pi_parcel_id"]

    # binding.pry
  end
end
